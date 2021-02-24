<?php


declare(strict_types=1);

namespace PackageUrl;

/**
 * @psalm-import-type TPUrlType from PackageUrl
 * @psalm-import-type TPUrlNamespace from PackageUrl
 * @psalm-import-type TPUrlName from PackageUrl
 * @psalm-import-type TPUrlVersion from PackageUrl
 * @psalm-import-type TPUrlQualifiers from PackageUrl
 * @psalm-import-type TPUrlSubpath from PackageUrl
 *
 * @author jkowalleck
 */
class PackageUrlBuilder {


    /**
     * @psalm-param non-empty-string $scheme
     * @psalm-param TPUrlType $type
     * @psalm-param TPUrlNamespace $namespace
     * @psalm-param TPUrlName $name
     * @psalm-param TPUrlVersion $version
     * @psalm-param string|null $qualifiers
     * @psalm-param TPUrlSubpath $subpath
     *
     * @psalm-return non-empty-string
     *
     * @TODO
     */
    public function build(string $scheme, string $type, ?string $namespace, string $name, ?string $version, ?string $qualifiers, ?string $subpath): string {
        return $scheme.
        ':'.$type.
        (null === $namespace ? '' : '/'.rawurlencode($namespace)).
        '/'.rawurlencode($name).
        (null === $version ? '' : '@'.rawurlencode($version)).
        (0 === count($qualifiers) ? '' : '?'.http_build_query($qualifiers)). // $TODO see specs
        (null === $subpath ? '' : '#'.$subpath);
    }
}
