<?php

namespace PackageUrl\Tests\unit;

use PackageUrl\PackageUrlParser;
use PHPUnit\Framework\TestCase;

use Generator;

/**
 * @covers \PackageUrl\PackageUrlParser
 *
 * @psalm-import-type TParsedTypeNamespaceNameVersion from PackageUrlParser
 * @psalm-import-type TDataSet from \PackageUrl\Tests\_data\TestSuiteData
 *
 * @author jkowalleck
 */
class PackageUrlParserTest extends TestCase
{

    /**
     * @dataProvider dpStringsToLowercase
     * @dataProvider dpStringsEmptyAndNull
     */
    public function testNormalizeScheme(?string $input, ?string $expectedOutput): void
    {
        $parser = new PackageUrlParser();
        $normalized = $parser->normalizeScheme($input);
        self::assertSame($expectedOutput, $normalized);
    }

    /**
     * @dataProvider dpStringsToLowercase
     * @dataProvider dpStringsEmptyAndNull
     */
    public function testNormalizeType(?string $input, ?string $expectedOutput): void
    {
        $parser = new PackageUrlParser();
        $normalized = $parser->normalizeType($input);
        self::assertSame($expectedOutput, $normalized);
    }


    /**
     * @dataProvider dpNormalizeNamespace
     * @dataProvider dpStringsEmptyAndNull
     */
    public function testNormalizeNamespace(?string $input, ?string $expectedOutput): void
    {
        $parser = new PackageUrlParser();
        $normalized = $parser->normalizeNamespace($input, null);
        self::assertSame($expectedOutput, $normalized);
    }

    /**
     * @dataProvider dpStringsToDecoded
     * @dataProvider dpStringsEmptyAndNull
     */
    public function testNormalizeName(?string $input, ?string $expectedOutput): void
    {
        $parser = new PackageUrlParser();
        $normalized = $parser->normalizeName($input, null);
        self::assertSame($expectedOutput, $normalized);
    }

    /**
     * @dataProvider dpStringsToDecoded
     * @dataProvider dpStringsEmptyAndNull
     */
    public function testNormalizeVersion(?string $input, ?string $expectedOutput): void
    {
        $parser = new PackageUrlParser();
        $normalized = $parser->normalizeVersion($input);
        self::assertSame($expectedOutput, $normalized);
    }

    /**
     * @dataProvider dpNormalizeQualifiers
     * @dataProvider dpStringsEmptyAndNull
     */
    public function testNormalizeQualifiers(?string $input, ?array $expectedOutcome): void
    {
        $parser = new PackageUrlParser();
        $normalized = $parser->normalizeQualifiers($input);
        self::assertSame($expectedOutcome, $normalized);
    }

    /**
     * @dataProvider dpNormalizeSubpath
     * @dataProvider dpStringsEmptyAndNull
     */
    public function testNormalizeSubpath(?string $input, ?string $expectedOutcome): void
    {
        $parser = new PackageUrlParser();
        $decoded = $parser->normalizeSubpath($input);
        self::assertSame($expectedOutcome, $decoded);
    }

    /**
     * @dataProvider \PackageUrl\Tests\_data\TestSuiteData::data
     * @psalm-param TDataSet $data
     */
    public function testParseAndNormalize(array $data): void
    {
        $parser = new PackageUrlParser();
        $expected = [
            'type' => $data['type'],
            'namespace' => $data['namespace'],
            'name' => $data['name'],
            'version' => $data['version'],
            'qualifiers' => $data['qualifiers'],
            'subpath' => $data['subpath'],
        ];

        $parsed = $parser->parse($data['purl']);
        $normalized = [
            'type' => $parser->normalizeType($parsed['type']),
            'namespace' => $parser->normalizeNamespace($parsed['namespace'], $parsed['type']),
            'name' => $parser->normalizeName($parsed['name'], $parsed['type']),
            'version' => $parser->normalizeVersion($parsed['version']),
            'qualifiers' => $parser->normalizeQualifiers($parsed['qualifiers']),
            'subpath' => $parser->normalizeSubpath($parsed['subpath']),
        ];

        if ($data['is_invalid']) {
            self::assertNotSame($expected, $normalized);
        } else {
            self::assertSame($expected,  $normalized);
        }
    }

    /**
     * @psalm-return Generator<string, array{string, string}>
     */
    public static function dpNormalizeNamespace(): Generator
    {
        yield 'empty/namespace' => ['/', null];
        yield 'some namespace' => ['some/Namespace','some/Namespace'];
        yield 'some/empty namespace' => ['some//Namespace','some/Namespace'];
        yield 'encoded namespace' => ['some/Name%20space', 'some/Name space'];
        yield 'complex namespace' => ['/yet/another//Name%20space/', 'yet/another/Name space'];
    }

    public static function dpStringsToDecoded(): Generator
    {
        yield 'some string' => ['someString','someString'];
        yield 'encoded string' => ['some%20%22encoded%22%20string', 'some "encoded" string'];
    }

    /**
     * @psalm-return Generator<string, array{string, array<string, string>}>
     */
    public static function dpNormalizeQualifiers(): Generator
    {
        yield 'some empty value' => ['k=', null];
        yield 'some none value' => ['k', null];
        yield 'some kv' => ['k=v', ['k'=>'v']];
        yield 'some KV' => ['k=V', ['k'=>'V']];
        yield 'some encoded value' => ['k=a%20value', ['k'=>'a value']];
        yield 'checksums' => ['checksum=sha1:1234567890123,md5:4567890123456789012', ['checksum' => ['sha1:1234567890123', 'md5:4567890123456789012']]];
        yield 'multiple KVs' => ['k1=v1&k2=v2&k3=&k4', ['k1'=>'v1', 'k2'=>'v2']];
    }

    /**
     * @psalm-return Generator<string, array{string, string}>
     */
    public static function dpNormalizeSubpath(): Generator
    {
        yield 'dot' => ['.', null];
        yield 'dot dot' => ['..', null];
        yield 'path' => ['path', 'path'];
        yield 'some/path' => ['some/path', 'some/path'];
        yield 'surrounding slashes' => ['/path//', 'path'];
        yield 'inner slashes' => ['some//path/', 'some/path'];
        yield 'encoded' => ['some%20path/', 'some path'];
        yield 'complex' => ['//.foo/./bar./..//Baz%20ooF/', '.foo/bar./Baz ooF'];
        yield 'dot complex' => ['/./..//./', null];
    }

    /**
     * @psalm-return Generator<string, array{string}>
     */
    public static function dpStringsToLowercase(): Generator
    {
        yield 'lowercase' => ['something', 'something'];
        yield 'UPPERCASE' => ['SOMETHING', 'something'];
        yield 'mIxeDCase' => ['sOmetHIng', 'something'];
    }

    public static function dpStringsEmptyAndNull(): Generator {
        yield 'empty' => ['', null];
        yield 'null' => [null, null];
    }

}
