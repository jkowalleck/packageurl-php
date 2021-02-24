<?php

declare(strict_types=1);

namespace PackageUrl;

use DomainException;

/**
 * A purl is a package URL as defined at
 * {@link https://github.com/package-url/purl-spec}.
 *
 * @psalm-type TQualifiers = array<non-empty-string, non-empty-string>
 * @psalm-type TType = non-empty-string
 * @psalm-type TNamespace = non-empty-string|null
 * @psalm-type TName = non-empty-string
 * @psalm-type TVersion = non-empty-string|null
 * @psalm-type TSubpath = non-empty-string|null
 *
 * @author jkowalleck
 */
class PackageUrl
{
    public const SCHEME = 'pkg';


    /**
     * @psalm-var TType
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $type;

    /**
     * @psalm-var TNamespace
     */
    private $namespace;

    /**
     * @psalm-var TName
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $name;

    /**
     * @psalm-var TVersion
     */
    private $version;

    /**
     * @psalm-var TQualifiers
     */
    private $qualifiers = [];

    /**
     * @psalm-var TSubpath
     */
    private $subpath;

    // region getters/setters

    /**
     * @psalm-return TType
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @psalm-param TType $type
     *
     * @throws DomainException if value is empty
     * @psalm-return  $this
     */
    public function setType(string $type): self
    {
        /** @psalm-suppress TypeDoesNotContainType implemented for non-psalm users */
        if ('' === $type) {
            throw new DomainException('Type must not be empty');
        }

        $this->type = strtolower($type);

        return $this;
    }

    /**
     * @psalm-return TNamespace
     */
    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    /**
     * @psalm-param TNamespace|string $namespace
     * @psalm-return $this
     */
    public function setNamespace(?string $namespace): self
    {
        $this->namespace = '' === $namespace ? null : $namespace;

        return $this;
    }

    /**
     * @psalm-return TName
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @psalm-param TName $name
     *
     * @throws DomainException if value is empty
     * @psalm-return $this
     */
    public function setName(string $name): self
    {
        /** @psalm-suppress TypeDoesNotContainType implemented for non-psalm users */
        if ('' === $name) {
            throw new DomainException('Name must not be empty');
        }
        $this->name = $name;

        return $this;
    }

    /**
     * @psalm-return TVersion
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }

    /**
     * @psalm-param TVersion|string $version
     * @psalm-return $this
     */
    public function setVersion(?string $version): self
    {
        $this->version = '' === $version ? null : $version;

        return $this;
    }

    /**
     * @psalm-return TQualifiers
     */
    public function getQualifiers(): array
    {
        return $this->qualifiers;
    }

    /**
     * @psalm-param TQualifiers $qualifiers
     * @psalm-return $this
     */
    public function setQualifiers(array $qualifiers): self
    {
        foreach ($qualifiers as $key => $value) {
            if (false === $this->validateQualifier($key, $value)) {
                unset($qualifiers[$key]);
            }
        }

        $this->qualifiers = $qualifiers;

        return $this;
    }

    /**
     * @psalm-return TSubpath
     */
    public function getSubpath(): ?string
    {
        return $this->subpath;
    }

    /**
     * @psalm-param TSubpath|string $subpath
     * @psalm-return $this
     */
    public function setSubpath(?string $subpath): self
    {
        $this->subpath = '' === $subpath ? null : $subpath;

        return $this;
    }


    // endregion getters/setters

    /**
     * @psalm-param array-key $key
     * @psalm-param mixed $value
     *
     * @throws DomainException
     *
     * @psalm-return bool
     *
     * @TODO not all rules were implemented, yet
     */
    private function validateQualifier($key, $value): bool
    {
        if (false === is_string($key) || '' === $key) {
            throw new DomainException("PURL qualifiers key is invalid: {$key}");
        }
        if (false === is_string($value)) {
            throw new DomainException("PURL qualifiers value for key '{$key}' is invalid: {$value}");
        }

        // as of rule: a `key=value` pair with an empty `value` is the same as no key/value at all for this key
        return '' !== $value;
    }

    /**
     * @throws DomainException if a value was invalid
     *
     * @psalm-param TType $type
     * @psalm-param TName $name
     *
     * @see settype()
     * @see setName()
     */
    public function __construct(string $type, string $name)
    {
        $this->setType($type);
        $this->setName($name);
    }

    /**
     * implementation is not yet completely conform to
     * {@link https://github.com/package-url/purl-spec/blob/master/README.rst#a-purl-is-a-url}.
     */
    public function __toString(): string
    {
        return self::SCHEME.
            ':'.$this->type.
            (null === $this->namespace ? '' : '/'.rawurlencode($this->namespace)).
            '/'.rawurlencode($this->name).
            (null === $this->version ? '' : '@'.rawurlencode($this->version)).
            (0 === count($this->qualifiers) ? '' : '?'.http_build_query($this->qualifiers)). // $TODO see specs
            (null === $this->subpath ? '' : '#'.$this->subpath);
    }

    /**
     * @throws DomainException if the data is invalid according to the specification
     * @psalm-return PackageUrl|null null when empty string is passed
     */
    public static function fromString(string $data, ?PackageUrlParser $parser = null): ?self
    {
        if ('' === $data) {
            return null;
        }

        $parser = $parser === null ? $parser : new PackageUrlParser();

        [
            'type' => $type,
            'name' => $name,
            'namespace' => $namespace,
            'version' => $version,
            'qualifiers' => $qualifiers,
            'subpath' => $subpath,
        ] = $parser->parse($data);

        return (new PackageUrl($type, $name))
            ->setNamespace($namespace)
            ->setVersion($version)
            ->setQualifiers($qualifiers)
            ->setSubpath($parser->normalizePath($subpath));
    }

}
