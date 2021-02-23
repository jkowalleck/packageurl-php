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
 *
 * @internal This class is not for public use and therefore is not covered by backwards compatibility.
 */
final class PackageUrlParser {

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

    private function normalizeScheme(string $string): string {
        return strtolower($string);
    }

    private function normalizeType(string $string): string {
        return $string;
    }

    private function normalizeNamespace(string $string): string {
        return $string;
    }

    private function normalizeName(string $string): string {
        return $string;
    }

    /**
     * @psalm-return TPUrlVersion
     */
    private function normalizeVersion(string $string): string {
        return $string;
    }

    private function normalizeQualifiers(string $string): array {
        return ['TPUrlODO' => $string];
    }


    public function normalizeSubpath(string $string): ?string {
        return '' === $string ? null : $string;
    }

    private function parseTypeNamespaceNameVersion (string $string): array {
        $string = trim($string, '/');

        $leftSlashPos = strpos($string, '/');
        if (false === $leftSlashPos) {
            $type = '';
        } else {
            $type = substr($string, 0, $leftSlashPos);
            $string = substr($string, $leftSlashPos+1);
        }

        $rightAtPos = strrpos($string, '@');
        if (false === $rightAtPos) {
            $version = '';
        } else {
            $version = substr($string, $rightAtPos+1);
            $string = substr($string, 0, $rightAtPos);
        }

        $rightSlashPos = strrpos($string, '@');
        if (false === $rightSlashPos) {
            $name = '';
        } else {
            $name = substr($string, $rightSlashPos+1);
            $string = substr($string, 0, $rightSlashPos);
        }

        $nameSpaces = explode('/', $string);

        $namespace = $string;
        return [$type, $namespace, $name, $version];
    }

    /*
    private function foo ()
    {
        $this->normalizeScheme($parts['scheme'] ?? '<MISSING>');
        if (PackageUrl::SCHEME !== $scheme) {
            throw new  DomainException("invalid scheme: ${$scheme}");
        }

        '<MISSING>'
        if (PackageUrl::SCHEME !== $scheme) {
            throw new DomainException("invalid schema: {$scheme}");
        }

        if (false === isset($parts['path'])) {
            throw new DomainException('missing path');
        }
        $partsPath = explode('@', $parts['path']);
        switch (count($partsPath)) {
            case 1:
                [$typeNamespaceName, $version] = [$partsPath[0], null];
                break;
            case 2:
                [$typeNamespaceName, $version] = $partsPath;
                break;
            default:
                throw new DomainException('malformed: type/?namespace/type@?version');
        }
        if ('' === $version) {
            $version = null;
        }

        $partsTypeNamespaceName = explode('/', $typeNamespaceName);
        switch (count($partsTypeNamespaceName)) {
            case 2:
                [$type, $namespace, $name] = [$partsTypeNamespaceName[0], null, $partsTypeNamespaceName[1]];
                break;
            case 3:
                [$type, $namespace, $name] = $partsTypeNamespaceName;
                break;
            default:
                throw new DomainException('malformed: type/namespace?/type');
        }

        $qualifiers = [];
        if (isset($parts['query'])) {
            parse_str($parts['query'], $qualifiers);
        }

        $subpath = $parts['fragment'] ?? '';
        if ('' === $subpath) {
            $subpath = null;
        }
    }
    */

}
