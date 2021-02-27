<?php

declare(strict_types=1);

namespace PackageUrl;

use DomainException;

/**
 * A purl is a package URL as defined at
 * {@link https://github.com/package-url/purl-spec}.
 *
 * @psalm-type TType = non-empty-string
 * @psalm-type TNamespace = non-empty-string|null
 * @psalm-type TName = non-empty-string
 * @psalm-type TVersion = non-empty-string|null
 * @psalm-type TQualifiers = null|array<non-empty-string, non-empty-string>
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
    private $qualifiers;

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
     *
     * @throws DomainException if value is empty
     * @psalm-return  $this
     */
    public function setType(string $type): self
    {
        if ('' === $type) {
            throw new DomainException('Type must not be empty');
        }
        $this->type = $type;

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
    public function getQualifiers(): ?array
    {
        return $this->qualifiers;
    }

    /**
     * @psalm-param TQualifiers $qualifiers
     * @psalm-return $this
     */
    public function setQualifiers(?array $qualifiers): self
    {
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
     * @psalm-return $this
     */
    public function setSubpath(?string $subpath): self
    {
        $this->subpath = '' === $subpath ? null : $subpath;

        return $this;
    }


    // endregion getters/setters

    /**
     * @throws DomainException if a value was invalid
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
        return $this->toString();
    }

    public function toString(?PackageUrlBuilder $builder = null): string
    {
        $builder = $builder ?? new PackageUrlBuilder();

        return $builder->build(
            self::SCHEME,
            $this->type,
            $this->namespace,
            $this->name,
            $this->version,
            $this->qualifiers,
            $this->subpath
        );
    }

    /**
     * @throws DomainException if the data is invalid according to the specification
     * @psalm-return static|null null when empty string is passed
     */
    public static function fromString(string $data, ?PackageUrlParser $parser = null): ?self
    {
        if ('' === $data) {
            return null;
        }

        $parser = $parser ?? new PackageUrlParser();

        [
            'scheme' => $scheme,
            'type' => $type,
            'name' => $name,
            'namespace' => $namespace,
            'version' => $version,
            'qualifiers' => $qualifiers,
            'subpath' => $subpath,
        ] = $parser->parse($data);

        if ($parser->normalizeScheme($scheme) !== self::SCHEME) {
            throw new DomainException("mismatching scheme '{$scheme}'");
        }

        $type = $parser->normalizeType($type);
        if ($type === null) {
            throw new DomainException("type cannot be empty");
        }

        $name = $parser->normalizeName($name, $type);
        if ($name === null) {
            throw new DomainException("name cannot be empty");
        }


        return (new static($type, $name))
            ->setNamespace($parser->normalizeNamespace($namespace, $type))
            ->setVersion($parser->normalizeVersion($version))
            ->setQualifiers($parser->normalizeQualifiers($qualifiers))
            ->setSubpath($parser->normalizeSubpath($subpath));
    }

}
