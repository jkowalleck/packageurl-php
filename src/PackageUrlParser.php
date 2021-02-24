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
 */
class PackageUrlParser
{

    /**
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

    /**
     * @TODO pretty much unoptimized. some parts may be done by the model.
     */
    public function normalizePath(string $subpath): string
    {
        $segments = explode('/', trim($subpath, '/'));
        $segments = array_filter($segments, Static function (string $segment): bool {
            return false === in_array($segment, ['', '.', '..'], true);
        });
        $segments = array_map(
            static function (string $segment): string {
                // utf8 encode transcode was left out for now, most php is running is utf8 already
                return rawurldecode($segment);
            },
            $segments
        );

        return implode('/', $segments);
    }

}
