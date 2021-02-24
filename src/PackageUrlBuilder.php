<?php


declare(strict_types=1);

namespace PackageUrl;

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
class PackageUrlBuilder {


    /**
     * @psalm-param non-empty-string $scheme
     * @psalm-param TType $type
     * @psalm-param TNamespace $namespace
     * @psalm-param TName $name
     * @psalm-param TVersion $version
     * @psalm-param TQualifiers $qualifiers
     * @psalm-param TSubpath $subpath
     *
     * @psalm-return non-empty-string
     *
     * @TODO see specs & implement
     */
    public function build(string $scheme, string $type, ?string $namespace, string $name, ?string $version, array $qualifiers, ?string $subpath): string {
        return $scheme.
        ':'.$type.
        (null === $namespace ? '' : '/'.rawurlencode($namespace)).
        '/'.rawurlencode($name).
        (null === $version ? '' : '@'.rawurlencode($version)).
        (0 === count($qualifiers) ? '' : '?'.http_build_query($qualifiers)).
        (null === $subpath ? '' : '#'.$subpath);
    }
}
