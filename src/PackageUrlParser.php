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
        $parts = '' === $data ? [] : parse_url($data);

        $scheme = $parts['scheme'] ?? '';
        $qualifiers = $parts['query'] ?? '';
        $subpath = $parts['fragment'] ?? '';
        [$type, $namespace, $name, $version] = $this->parseTypeNamespaceNameVersion($parts['path'] ?? '');

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
     * @psalm-return array{0|1|2|3: string}
     */
    private function parseTypeNamespaceNameVersion(string $data): array
    {
        $data = trim($data, '/');
        if ('' === $data) {
            return ['', '', '', ''];
        }

        $leftSlashPos = strpos($data, '/');
        if (false === $leftSlashPos) {
            $type = $data;
            $remainder = '';
        } else {
            $type = substr($data, 0, $leftSlashPos);
            $remainder = substr($data, $leftSlashPos + 1);
        }

        $rightAtPos = strrpos($remainder, '@');
        if (false === $rightAtPos) {
            $version = '';
        } else {
            $version = substr($remainder, $rightAtPos + 1);
            $remainder = substr($remainder, 0, $rightAtPos);
        }

        $rightSlashPos = strrpos($remainder, '/');
        if (false === $rightSlashPos) {
            $name = $remainder;
            $remainder = '';
        } else {
            $name = substr($remainder, $rightSlashPos + 1);
            $remainder = substr($remainder, 0, $rightSlashPos);
        }

        $namespace = $remainder;

        return [$type, $namespace, $name, $version];
    }


    // endregion parse


    // region normalize

    public function normalizeScheme(string $data): ?string
    {
        return '' === $data ? null : strtolower($data);
    }

    /**
     * @return non-empty-string|null
     */
    public function normalizeType(string $data): ?string
    {
        return '' === $data ? null : strtolower($data);
    }

    /**
     * @return non-empty-string|null
     */
    public function normalizeNamespace(string $data, ?string $type): ?string
    {
        if ('' === $data) {
            return null;
        }

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
    public function normalizeName(string $data, ?string $type): ?string
    {
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
    public function normalizeVersion(string $data): ?string
    {
         $version = rawurldecode($data);
        return '' === $version ? null : $version;
    }

    /**
     * @psalm-return non-empty-array|null
     */
    public function normalizeQualifiers(string $data): ?array
    {
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
            $key = rawurldecode(strtolower(substr($dataKeyValue, 0, $eqPos)));
            if ($key === 'checksum') {
                $value = explode(',', $value);
            }
            $qualifiers[$key] = $value;
        }

        return 0 === count($qualifiers) ? null : $qualifiers;
    }

    /**
     * @psalm-return non-empty-string|null
     */
    public function normalizeSubpath(string $data): ?string
    {
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

        return '' === $subpath ? null : $subpath;
    }

    // endregion normalize

}
