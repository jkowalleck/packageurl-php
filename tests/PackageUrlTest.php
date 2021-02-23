<?php

declare(strict_types=1);

namespace PackageUrl\Tests;

use PackageUrl\PackageUrl;
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

}
