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

use DomainException;

/**
 * @psalm-import-type TType from PackageUrl
 * @psalm-import-type TNamespace from PackageUrl
 * @psalm-import-type TName from PackageUrl
 * @psalm-import-type TVersion from PackageUrl
 * @psalm-import-type TQualifiers from PackageUrl
 * @psalm-import-type TSubpath from PackageUrl
 *
 * @author jkowalleck
 */
class PackageUrlBuilder
{
    /**
     * @psalm-param string $type
     * @psalm-param string|null $namespace
     * @psalm-param string $name
     * @psalm-param string|null $version
     * @psalm-param string|null $qualifiers
     * @psalm-param string|null $subpath
     *
     * @psalm-return non-empty-string
     *
     * @TODO see specs & implement
     */
    public function build(string $type, ?string $namespace, string $name, ?string $version, ?array $qualifiers, ?string $subpath): string
    {
        if ('' === $type) {
            throw new DomainException("Type must not be empty");
        }
        if ('' === $name) {
            throw new DomainException("Name must not be empty");
        }
        $type = $this->normalizeType($type);
        $namespace = $this->normalizeNamespace($namespace, $type);
        $name = $this->normalizeName($name, $type);
        $version = $this->normalizeVersion($version);
        $qualifiers = $this->normalizeQualifiers($qualifiers);
        $subpath = $this->normalizeSubpath($subpath);
        return PackageUrl::SCHEME.
            ':'. $type.
            (null === $namespace ? '' : '/'.$namespace).
            '/'.$name.
            (null === $version ? '' : '@'.$version).
            (null === $qualifiers ? '' : '?'.$qualifiers).
            (null === $subpath ? '' : '#'.$subpath)
            ;
    }

    /**
     * @psalm-param non-empty-string $data
     * @psalm-return non-empty-string
     */
    public function normalizeType(string $data): string
    {
        return strtolower($data);
    }

    /**
     * @psalm-return non-empty-string|null
     */
    public function normalizeNamespace(?string $data, string $type): ?string {
        if ($data === null) { return null;}

        $data = trim($data, '/');
        $segments = explode('/', $data);
        // @TODO Apply type-specific normalization to each segment if needed
        $segments = array_map([$this, 'encode'], $segments);
        $namespace = implode('/', $segments);

        return '' === $namespace
            ? null
            : $namespace;
    }

    /**
     * @psalm-param non-empty-string $data
     */
    public function normalizeName(string $data, string $type): string
    {
        $data = trim($data, '/');
        // @TODO Apply type-specific normalization to each segment if needed
        return $this->encode($data);
    }

    /**
     * @psalm-return non-empty-string|null
     */
    public function normalizeVersion(?string $data): ?string
    {
        if ($data === null) { return null;}

        return $this->encode($data);
    }

    /**
     * @psalm-return non-empty-string|null
     */
    public function normalizeQualifiers(?array $data): ?string
    {
        if ($data === null) { return null;}

        $qualifiers = [];
        foreach ($data as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            if ('checksum' === $key && is_array($value)) {
                $value = implode(',', $value);
            }
            $qualifiers[] = strtolower((string) $key) . '='. $this->encode((string) $value);
        }

        if (0 === count($qualifiers) ) {
            return null;
        }
        sort($qualifiers, SORT_STRING);
        return implode('&', $qualifiers);
    }

    use BuildParseTrait;

    /**
     * @psalm-return non-empty-string|null
     */
    public function normalizeSubpath(?string $data): ?string
    {
        if ($data === null) { return null;}
        $data = trim($data, '/');
        $segments = explode('/', $data);
        /** @see BuildParseTrait::isUsefulSubpathSegment() */
        $segments = array_filter($segments, [$this, 'isUsefulSubpathSegment']);
        $segments = array_map([$this, 'encode'], $segments);
        if (0 === count($segments) ) {
            return null;
        }
        return implode('/', $segments);
    }

    /**
     * Revert special chars that must not be encoded.
     * See {@link https://github.com/package-url/purl-spec#character-encoding Character encoding}.
     */
    private const RAWURLENCODE_REVERT = [
        '%3A' => ':',
        '%2F' => '/'
    ];

    private function encode(string $data): string
    {
        $data = rawurlencode($data);
        return strtr($data, self::RAWURLENCODE_REVERT);
    }
}
