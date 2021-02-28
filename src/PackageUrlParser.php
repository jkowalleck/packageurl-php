<?php

declare(strict_types=1);

/*
 * Copyright (c) the purl authors
 * SPDX-License-Identifier: MIT
 * MIT License
 *
 * Copyright (c) 2021 package-url
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * Visit https://github.com/package-url/packageurl-php
 * for support and download.
 */

namespace PackageUrl;

/**
 * A purl is a package URL as defined at
 * {@link https://github.com/package-url/purl-spec}.
 *
 * @author jkowalleck
 *
 * @TODO addd proper types from model
 */
class PackageUrlParser
{
    use BuildParseTrait;

    // region parse

    /**
     * Does pure parsing.
     * Normalization/decoding is done with other methods from this class.
     *
     * @psalm-return array{
     *      type: string|null,
     *      namespace: string|null,
     *      name: string|null,
     *      version: string|null,
     *      qualifiers: string|null,
     *      subpath: string|null,
     * }
     */
    public function parse(string $data): array
    {
        $remainder = $data;

        // used custom parse, since `parse_url` had issues with multiple leading slashes
        [$subpath, $remainder] = $this->splitRightOn('#', $remainder, false);
        [$qualifiers, $remainder] = $this->splitRightOn('?', $remainder, false);
        [$scheme, $remainder] = $this->splitLeftOn(':', $remainder, true);
        $remainder = trim($remainder, '/');
        [$type, $remainder] = $this->splitLeftOn('/', $remainder, true);
        [$version, $remainder] = $this->splitRightOn('@', $remainder, false);
        [$name, $remainder] = $this->splitRightOn('/', $remainder, true);
        $namespace = '' === $remainder ? null : $remainder;

        return [
            'scheme' => $scheme,
            'type' => $type,
            'namespace' => $namespace,
            'name' => $name,
            'version' => $version,
            'qualifiers' => $qualifiers,
            'subpath' => $subpath,
        ];
    }

    /**
     * @psalm-return array{string|null, string}
     */
    private function splitRightOn(string $chr, string $data, bool $rightRequired): array
    {
        $pos = strrpos($data, $chr);
        if (false !== $pos) {
            return [substr($data, $pos + 1), substr($data, 0, $pos)];
        }

        return $rightRequired
            ? [$data, '']
            : [null, $data];
    }

    /**
     * @psalm-return array{string|null, string}
     */
    private function splitLeftOn(string $chr, string $data, bool $leftRequired): array
    {
        $pos = strpos($data, $chr);
        if (false !== $pos) {
            return [substr($data, 0, $pos), substr($data, $pos + 1)];
        }

        return $leftRequired
            ? [$data, '']
            : [null, $data];
    }

    // endregion parse

    // region normalize

    public function normalizeScheme(?string $data): ?string
    {
        if (null === $data) {
            return null;
        }

        return '' === $data
            ? null
            : strtolower($data);
    }

    /**
     * @return non-empty-string|null
     */
    public function normalizeType(?string $data): ?string
    {
        if (null === $data) {
            return null;
        }

        return '' === $data
            ? null
            : strtolower($data);
    }

    /**
     * @return non-empty-string|null
     */
    public function normalizeNamespace(?string $data, ?string $type): ?string
    {
        if (null === $data) {
            return null;
        }
        if ('' === $data) {
            return null;
        }

        $segments = explode('/', trim($data, '/'));
        $segments = array_filter($segments, [$this, 'isNotEmpty']);
        $segments = array_map('rawurldecode', $segments);
        $segments = array_map($this->getNormalizerForNamespace($type), $segments);

        $namespace = implode('/', $segments);
        if ('' === $namespace) {
            return null;
        }

        return $namespace;
    }

    /**
     * @return non-empty-string|null
     */
    public function normalizeName(?string $data, ?string $type): ?string
    {
        if (null === $data) {
            return null;
        }
        $name = rawurldecode($data);
        if ('' === $name) {
            return null;
        }

        return $this->normalizeNameForType($name, $type);
    }

    /**
     * @return non-empty-string|null
     */
    public function normalizeVersion(?string $data): ?string
    {
        if (null === $data) {
            return null;
        }

        $version = rawurldecode($data);

        return '' === $version
            ? null
            : $version;
    }

    /**
     * @psalm-return non-empty-array<non-empty-string, non-empty-string>|null
     */
    public function normalizeQualifiers(?string $data): ?array
    {
        if (null === $data) {
            return null;
        }

        if ('' === $data) {
            return null;
        }

        /** @var array<non-empty-string, non-empty-string> $qualifiers */
        $qualifiers = [];
        foreach (explode('&', $data) as $dataKeyValue) {
            $eqPos = strpos($dataKeyValue, '=');
            if (false === $eqPos || 0 === $eqPos) {
                continue;
            }
            $value = rawurldecode(substr($dataKeyValue, $eqPos + 1));
            if ('' === $value) {
                continue;
            }
            $key = strtolower(substr($dataKeyValue, 0, $eqPos));
            assert('' !== $key);
            $qualifiers[$key] = $value;
        }

        return empty($qualifiers)
            ? null
            : $qualifiers;
    }

    /**
     * @psalm-return non-empty-string|null
     */
    public function normalizeSubpath(?string $data): ?string
    {
        if (null === $data) {
            return null;
        }
        if ('' === $data) {
            return null;
        }

        $segments = explode('/', trim($data, '/'));
        /** @see BuildParseTrait::isUsefulSubpathSegment() */
        $segments = array_filter($segments, [$this, 'isUsefulSubpathSegment']);
        $segments = array_map('rawurldecode', $segments);
        $subpath = implode('/', $segments);

        return '' === $subpath
            ? null
            : $subpath;
    }

    // endregion normalize
}
