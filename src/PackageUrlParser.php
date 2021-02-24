<?php

declare(strict_types=1);

namespace PackageUrl;

use DomainException;
use phpDocumentor\Reflection\Types\Static_;

/**
 * A purl is a package URL as defined at
 * {@link https://github.com/package-url/purl-spec}.
 *
 * @psalm-import-type TPUrlType from PackageUrl
 * @psalm-import-type TPUrlNamespace from PackageUrl
 * @psalm-import-type TPUrlName from PackageUrl
 * @psalm-import-type TPUrlVersion from PackageUrl
 * @psalm-import-type TPUrlQualifiers from PackageUrl
 * @psalm-import-type TPUrlSubpath from PackageUrl
 *
 * @psalm-type TParsed = array{type: string, namespace: string, name: string, version: string, qualifiers: string, subpath: string}
 * @psalm-type TParsedTypeNamespaceNameVersion = array{string, string, string, string}
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
     * @psalm-return TParsedTypeNamespaceNameVersion
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

    public function normalizeScheme(string $data): string {
        return strtolower($data);
    }

    public function normalizeType(string $data): string {
        return strtolower($data);
    }

    public function normalizeNamespace(string $data): ?string {
        if ('' === $data) {
            return null;
        }

        $parts = explode('/', trim($data, '/'));
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

    public function normalizeName(string $data): string {
        return rawurldecode($data);
    }

    public function normalizeVersion(string $data): string {
        if ('' === $data) {
            return $data;
        }

        return rawurldecode($data);
    }

    /**
     * @param string $data
     * @return array
     */
    public function normalizeQualifiers(string $data): array {
        return []; // @TODO
    }

    public function normalizeSubpath(string $data): ?string
    {
        if ('' === $data) {
            return null;
        }

        $parts = explode('/', trim($data, '/'));
        $parts = array_filter($parts, Static function (string $part): bool {
            return false === in_array($part, ['', '.', '..'], true);
        });
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
