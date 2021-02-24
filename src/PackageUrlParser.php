<?php

declare(strict_types=1);

namespace PackageUrl;

use DomainException;
use phpDocumentor\Reflection\Types\Static_;

/**
 * A purl is a package URL as defined at
 * {@link https://github.com/package-url/purl-spec}.
 *
 * @psalm-import-type TType from PackageUrl
 * @psalm-import-type TNamespace from PackageUrl
 * @psalm-import-type TName from PackageUrl
 * @psalm-import-type TVersion from PackageUrl
 * @psalm-import-type TQualifiers from PackageUrl
 * @psalm-import-type TSubpath from PackageUrl
 *
 * @psalm-type TParsed = array{type: string, namespace: string, name: string, version: string, qualifiers: string, subpath: string}
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
     * @psalm-param non-empty-string $data
     *
     * @throws DomainException if scheme mismatches the specs
     *
     * @psalm-return TParsed
     */
    public function parse(string $data): array
    {
        $parts = parse_url($data);

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
     * @psalm-return array{string, string, string, string}
     */
    private function parseTypeNamespaceNameVersion(string $string): array
    {
        $string = trim($string, '/');

        $leftSlashPos = strpos($string, '/');
        if (false === $leftSlashPos) {
            $type = $string;
            $remainder = '';
        } else {
            $type = substr($string, 0, $leftSlashPos);
            $remainder = substr($string, $leftSlashPos + 1);
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

    public function normalizeScheme(string $data): string
    {
        return strtolower($data);
    }

    /**
     * @psalm-return TType|string
     */
    public function normalizeType(string $data): string
    {
        return strtolower($data);
    }

    /**
     * @psalm-return TNamespace
     */
    public function normalizeNamespace(string $data): ?string
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

        return '' === $namespace ? null : $namespace;
    }

    /**
     * @psalm-return TName|string
     */
    public function normalizeName(string $data): string
    {
        return rawurldecode($data);
    }

    /**
     * @psalm-return TVersion
     */
    public function normalizeVersion(string $data): ?string
    {
        if ('' === $data) {
            return null;
        }

        return rawurldecode($data);
    }

    /**
     * @psalm-return TQualifiers
     */
    public function normalizeQualifiers(string $data): array
    {
        $qualifiers = [];
        if ('' === $data) {
            return $qualifiers;
        }

        foreach (explode('&', $data) as $dataKeyValue) {
            $eqPos = strpos($dataKeyValue, '=');
            if (false === $eqPos || 0 === $eqPos) {
                continue;
            }
            $value = rawurldecode(substr($dataKeyValue, $eqPos+1));
            if ($value === '' ) {
                continue;
            }
            $key = strtolower(substr($dataKeyValue, 0, $eqPos));
            if ($key === 'checksum') {
                $value = explode(',', $value);
            }
            $qualifiers[$key] = $value;
        }

        return $qualifiers;
    }

    /**
     * @psalm-return TSubpath
     */
    public function normalizeSubpath(string $data): ?string
    {
        if ('' === $data) {
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
