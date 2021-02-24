<?php

declare(strict_types=1);

namespace PackageUrl;

use DomainException;

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
 */
final class PackageUrlParser
{

    /**
     * @psalm-param non-empty-string $data
     *
     * @throws DomainException if scheme mismatches the specs
     *
     * @psalm-return array{type: string, namespace: string, name: string, version: string, qualifiers: string, subpath: string}
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

    private function parseTypeNamespaceNameVersion(string $string): array
    {
        $string = trim($string, '/');

        $leftSlashPos = strpos($string, '/');
        if (false === $leftSlashPos) {
            $type = '';
        } else {
            $type = substr($string, 0, $leftSlashPos);
            $string = substr($string, $leftSlashPos + 1);
        }

        $rightAtPos = strrpos($string, '@');
        if (false === $rightAtPos) {
            $version = '';
        } else {
            $version = substr($string, $rightAtPos + 1);
            $string = substr($string, 0, $rightAtPos);
        }

        $rightSlashPos = strrpos($string, '/');
        if (false === $rightSlashPos) {
            $name = $string;
            $namespace = '';
        } else {
            $name = substr($string, $rightSlashPos + 1);
            $namespace = substr($string, 0, $rightSlashPos);
        }

        return [$type, $namespace, $name, $version];
    }

    /**
     * @TODO pretty much unoptimized. some parts may be done by the model.
     */
    public function urldecodePath(string $subpath): string
    {
        return implode(
            '/',
            array_map(
                static function (string $part): string {
                    // utf8 encode transcode was left out for now, most php is running is utf8 already
                    return rawurlencode($part);
                },
                array_filter(
                    explode(
                        '/',
                        trim($subpath, '/')
                    ),
                    static function (string $part): bool {
                        return false !== in_array($part, ['', '.', '..'], true);
                    }
                )
            )
        );
    }

}
