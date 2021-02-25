<?php

declare(strict_types=1);

namespace PackageUrl;

use DomainException;

/**
 * A purl is a package URL as defined at
 * {@link https://github.com/package-url/purl-spec}.
 *
 * @author jkowalleck
 */
class PackageUrlParser
{

    // region parse

    /**
     * Does pure parsing.
     * Normalization/decoding is done with other methods from this class.
     *
     * @throws DomainException if scheme mismatches the specs
     *
     * @psalm-return array{type|namespace|name|version|qualifiers|subpath: string}|null
     */
    public function parse(string $data): array
    {
        $remainder = $data;

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
        if (false !== $pos){
            return [substr($data, $pos+1), substr($data, 0, $pos)];
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
        if ( false !== $pos ) {
            return [substr($data, 0, $pos), substr($data, $pos+1)];
        }
        return $leftRequired
            ? [$data, '']
            : [null, $data];
    }

    // endregion parse


    // region normalize

    public function normalizeScheme(?string $data): ?string
    {
        if (null === $data) { return $data; }
        return '' === $data
            ? null
            : strtolower($data);
    }

    /**
     * @return non-empty-string|null
     */
    public function normalizeType(?string $data): ?string
    {
        if (null === $data) { return $data; }
        return '' === $data
            ? null
            : strtolower($data);
    }

    /**
     * @return non-empty-string|null
     */
    public function normalizeNamespace(?string $data, ?string $type): ?string
    {
        if (null === $data) { return $data; }
        if ('' === $data) { return null; }

        $parts = explode('/', trim($data, '/'));
        $parts = array_filter(
            $parts,
            static function (string $part): bool {
                return '' !== $part;
            }
        );
        $parts = array_map(
            static function (string $part): string {
                // utf8 encode transcode was left out for now, most php is running is utf8 already
                return rawurldecode($part);
            },
            $parts
        );

        $namespace = implode('/', $parts);
        if ('' === $namespace) {
            return null;
        }

        $type = null === $type ? null : $this->normalizeType($type);

        return in_array($type, ['bitbucket', 'deb', 'github', 'golang', 'hex', 'rpm'], true)
            ? strtolower($namespace)
            : $namespace;

    }

    /**
     * @return non-empty-string|null
     */
    public function normalizeName(?string $data, ?string $type): ?string
    {
        if (null === $data) { return $data; }
        $name = rawurldecode($data);
        if ('' === $name) {
            return null;
        }

        $type = null === $type ? null : $this->normalizeType($type);

        if ('pypi' === $type)  {
            $name = str_replace('_', '-', $name);
        }

        return in_array($type, ['bitbucket', 'deb', 'github', 'golang', 'hex', 'npm', 'pypi'], true)
            ? strtolower($name)
            : $name;
    }

    /**
     * @return non-empty-string|null
     */
    public function normalizeVersion(?string $data): ?string
    {
        if (null === $data) { return $data; }

        $version = rawurldecode($data);
        return '' === $version
            ? null
            : $version;
    }

    /**
     * @psalm-return non-empty-array|null
     */
    public function normalizeQualifiers(?string $data): ?array
    {
        if (null === $data) { return $data; }

        if ( '' === $data) {
            return null;
        }

        $qualifiers = [];
        foreach (explode('&', $data) as $dataKeyValue) {
            $eqPos = strpos($dataKeyValue, '=');
            if (false === $eqPos || 0 === $eqPos) {
                continue;
            }
            $value = rawurldecode(substr($dataKeyValue, $eqPos + 1));
            if ($value === '') {
                continue;
            }
            $key = strtolower(substr($dataKeyValue, 0, $eqPos));
            if ($key === 'checksum') {
                $value = explode(',', $value);
            }
            $qualifiers[$key] = $value;
        }

        return 0 === count($qualifiers)
            ? null
            : $qualifiers;
    }

    /**
     * @psalm-return non-empty-string|null
     */
    public function normalizeSubpath(?string $data): ?string
    {
        if (null === $data) { return $data; }
        if ( '' === $data) {
            return null;
        }

        $parts = explode('/', trim($data, '/'));
        $parts = array_filter(
            $parts,
            static function (string $part): bool {
                return false === in_array($part, ['', '.', '..'], true);
            }
        );
        $parts = array_map(
            static function (string $part): string {
                // utf8 encode transcode was left out for now, most php is running is utf8 already
                return rawurldecode($part);
            },
            $parts
        );

        $subpath = implode('/', $parts);

        return '' === $subpath
            ? null
            : $subpath;
    }

    // endregion normalize

}
