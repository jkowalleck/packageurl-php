<?php

declare(strict_types=1);

namespace PackageUrl\Tests\unit;

use PackageUrl\PackageUrl;
use PackageUrl\PackageUrlParser;
use PHPUnit\Framework\TestCase;
use DomainException;

/**
 * @covers \PackageUrl\PackageUrl
 */
class PackageUrlTest extends TestCase
{
    /** @var PackageUrl */
    private $sut;

    public function setUp(): void
    {
        $randomString = bin2hex(random_bytes(255));
        $this->sut = (new PackageUrl($randomString, $randomString))
            ->setNamespace($randomString)
            ->setType($randomString)
            ->setQualifiers([$randomString => $randomString])
            ->setSubpath($randomString);
    }

    // region type setter&getter

    public function testTypeSetterGetter(): void
    {
        $value = bin2hex(random_bytes(random_int(3, 32)));
        $this->sut->setType($value);
        self::assertSame($value, $this->sut->getType());
    }

    public function testTypeSetterInvalid(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/empty/i');
        $this->sut->setType('');
    }

    // endregion type setter&getter

    // region name setter&getter

    public function testNameSetterGetter(): void
    {
        $value = bin2hex(random_bytes(random_int(3, 32)));
        $this->sut->setName($value);
        self::assertSame($value, $this->sut->getName());
    }

    public function testNameSetterInvalid(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/empty/i');
        $this->sut->setName('');
    }

    // endregion name setter&getter

    // region namespace setter&getter

    public function testNamespaceSetterGetter(): void
    {
        $value = bin2hex(random_bytes(random_int(3, 32)));
        $this->sut->setNamespace($value);
        self::assertSame($value, $this->sut->getNamespace());
    }

    public function testNamespaceSetterEmpty(): void
    {
        $this->sut->setNamespace('');
        self::assertNull($this->sut->getNamespace());
    }

    public function testNamespaceSetterNull(): void
    {
        $this->sut->setNamespace(null);
        self::assertNull($this->sut->getNamespace());
    }

    // endregion namespace setter&getter

    // region version setter&getter

    public function testVersionSetterGetter(): void
    {
        $value = bin2hex(random_bytes(random_int(3, 32)));
        $this->sut->setVersion($value);
        self::assertSame($value, $this->sut->getVersion());
    }

    public function testVersionSetterEmpty(): void
    {
        $this->sut->setVersion('');
        self::assertNull($this->sut->getVersion());
    }

    public function testVersionSetterNull(): void
    {
        $this->sut->setVersion(null);
        self::assertNull($this->sut->getVersion());
    }

    // endregion version setter&getter

    // region Qualifiers setter&getter

    public function testQualifiersSetterGetter(): void
    {
        $qualifiers = ['v'.bin2hex(random_bytes(32)) => 'k'.bin2hex(random_bytes(32))];
        $this->sut->setQualifiers($qualifiers);
        self::assertEquals($qualifiers, $this->sut->getQualifiers());
    }

    // endregion Qualifiers setter&getter

    // region subpath setter&getter

    public function testSubpathSetterGetter(): void
    {
        $value = bin2hex(random_bytes(random_int(3, 32)));
        $this->sut->setSubpath($value);
        self::assertSame($value, $this->sut->getSubpath());
    }

    public function testSubpathSetterEmpty(): void
    {
        $this->sut->setSubpath('');
        self::assertNull($this->sut->getSubpath());
    }

    public function testSubpathSetterNull(): void
    {
        $this->sut->setSubpath(null);
        self::assertNull($this->sut->getSubpath());
    }

    // endregion subpath setter&getter

    // region fromString

    public function testFromStringEmpty(): void
    {
        $parser = $this->createMock(PackageUrlParser::class);
        $purl = $this->sut::fromString('', $parser);
        self::assertNull($purl);
    }

    public function testFromString(): void {
        // arrange
        $purlString = $this->sut::SCHEME.':type/namespace/name@version?qualifiers=true#subpath';
        $purlParsed = [
            'scheme' => uniqid('parsedScheme', true),
            'type' => uniqid('parsedType', true),
            'namespace' => uniqid('parsedNamespace', true),
            'name' => uniqid('parsedName', true),
            'version' => uniqid('parsedVersion', true),
            'qualifiers' => uniqid('parsedQualifiers', true),
            'subpath' => uniqid('parsedSubpath', true),
        ];
        $purlNormalized = [
            'scheme' => $this->sut::SCHEME,
            'type' => uniqid('normalizedType', true),
            'namespace' => uniqid('normalizedNamespace', true),
            'name' => uniqid('normalizedName', true),
            'version' => uniqid('normalizedVersion', true),
            'qualifiers' => [uniqid('normalizedKeyQualifiers', true) => uniqid('normalizedValue', true)],
            'subpath' => uniqid('normalizedSubpath', true),
        ];
        $parser = $this->createMock(PackageUrlParser::class);
        $normalizeWithType = self::logicalOr($purlParsed['type'], $purlNormalized['type']);
        $parser->expects(self::once())->method('parse')->with($purlString)->willReturn($purlParsed);
        $parser->method('normalizeScheme')->with($purlParsed['scheme'])->willReturn($purlNormalized['scheme']);
        $parser->method('normalizeType')->with($purlParsed['type'])->willReturn($purlNormalized['type']);
        $parser->method('normalizeNamespace')->with($purlParsed['namespace'], $normalizeWithType)->willReturn($purlNormalized['namespace']);
        $parser->method('normalizeName')->with($purlParsed['name'], $normalizeWithType)->willReturn($purlNormalized['name']);
        $parser->method('normalizeVersion')->with($purlParsed['version'])->willReturn($purlNormalized['version']);
        $parser->method('normalizeQualifiers')->with($purlParsed['qualifiers'])->willReturn($purlNormalized['qualifiers']);
        $parser->method('normalizeSubpath')->with($purlParsed['subpath'])->willReturn($purlNormalized['subpath']);
        // act
        $purl = $this->sut::fromString($purlString, $parser);
        // assert
        self::assertInstanceOf(get_class($this->sut), $purl);
        self::assertEquals($purlNormalized['type'], $purl->getType());
        self::assertEquals($purlNormalized['namespace'], $purl->getNamespace());
        self::assertEquals($purlNormalized['name'], $purl->getName());
        self::assertEquals($purlNormalized['version'], $purl->getVersion());
        self::assertEquals($purlNormalized['qualifiers'], $purl->getQualifiers());
        self::assertEquals($purlNormalized['subpath'], $purl->getSubpath());
    }

    // endregion fromString

}
