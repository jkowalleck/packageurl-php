<?php

namespace PackageUrl\Tests\unit;

use PackageUrl\PackageUrlParser;
use PHPUnit\Framework\TestCase;

use ReflectionClass;
use Generator;

/**
 * @covers \PackageUrl\PackageUrlParser
 *
 * @psalm-import-type TParsedTypeNamespaceNameVersion from PackageUrlParser
 *
 * @author jkowalleck
 */
class PackageUrlParserTest extends TestCase
{

    /**
     * @dataProvider dpTypeNamespaceNameVersion
     *
     * @psalm-param TParsedTypeNamespaceNameVersion $expectedOutput
     */
    public function testParseTypeNamespaceNameVersion(string $input, array $expectedOutput): void
    {
        $parser = new PackageUrlParser();

        /** @see PackageUrlParser::parseTypeNamespaceNameVersion() */
        $parseTypeNamespaceNameVersion = (new ReflectionClass($parser))->getMethod('parseTypeNamespaceNameVersion');
        $parseTypeNamespaceNameVersion->setAccessible(true);

        $output = $parseTypeNamespaceNameVersion->invoke($parser, $input);

        self::assertSame($expectedOutput, $output);
    }


    /**
     * @dataProvider dpStringsToLowercase
     */
    public function testNormalizeScheme(string $input, string $expectedOutput): void
    {
        $parser = new PackageUrlParser();
        $normalized = $parser->normalizeScheme($input);
        self::assertSame($expectedOutput, $normalized);
    }

    /**
     * @dataProvider dpStringsToLowercase
     */
    public function testNormalizeType(string $input, string $expectedOutput): void
    {
        $parser = new PackageUrlParser();
        $normalized = $parser->normalizeType($input);
        self::assertSame($expectedOutput, $normalized);
    }


    /**
     * @dataProvider dpNormalizeNamespace
     * @dataProvider dpStringsEmptyToNull
     */
    public function testNormalizeNamespace(string $input, ?string $expectedOutput): void
    {
        $parser = new PackageUrlParser();
        $normalized = $parser->normalizeNamespace($input, null);
        self::assertSame($expectedOutput, $normalized);
    }

    /**
     * @dataProvider dpStringsToDecoded
     */
    public function testNormalizeName(string $input, string $expectedOutput): void
    {
        $parser = new PackageUrlParser();
        $normalized = $parser->normalizeName($input, null);
        self::assertEquals($expectedOutput, $normalized);
    }

    /**
     * @dataProvider dpStringsToDecoded
     * @dataProvider dpStringsEmptyToNull
     */
    public function testNormalizeVersion(string $input, ?string $expectedOutput): void
    {
        $parser = new PackageUrlParser();
        $normalized = $parser->normalizeVersion($input);
        self::assertSame($expectedOutput, $normalized);
    }

    /**
     * @dataProvider dpNormalizeQualifiers
     */
    public function testNormalizeQualifiers(string $input, ?array $expectedOutcome): void
    {
        $parser = new PackageUrlParser();
        $normalized = $parser->normalizeQualifiers($input);
        self::assertEquals($expectedOutcome, $normalized);
    }

    /**
     * @dataProvider dpNormalizeSubpath
     * @dataProvider dpStringsEmptyToNull
     */
    public function testNormalizeSubpath(string $input, ?string $expectedOutcome): void
    {
        $parser = new PackageUrlParser();
        $decoded = $parser->normalizeSubpath($input);
        self::assertSame($expectedOutcome, $decoded);
    }


    /**
     * @psalm-return Generator<string, array{string, TParsedTypeNamespaceNameVersion}>
     */
    public static function dpTypeNamespaceNameVersion(): Generator
    {
        yield 'empty' => ['', ['', '', '', '']];
        yield 'type' => ['someType', ['someType', '', '', '']];
        yield 'type, name' => ['someType/someName', ['someType', '', 'someName', '']];
        yield 'type, name, version' => ['someType/someName@someVersion', ['someType', '', 'someName', 'someVersion']];
        yield 'type, namespace, name' => [
            'someType/someNamespace/someName',
            ['someType', 'someNamespace', 'someName', ''],
        ];
        yield 'type, name/space, name' => [
            'someType/some/Name/space/someName',
            ['someType', 'some/Name/space', 'someName', ''],
        ];
        yield 'type, namespace, name, version' => [
            'someType/someNamespace/someName@someVersion',
            ['someType', 'someNamespace', 'someName', 'someVersion'],
        ];
    }

    /**
     * @psalm-return Generator<string, array{string, string}>
     */
    public static function dpNormalizeNamespace(): Generator {
        yield 'empty/namespace' => ['/', null];
        yield 'some namespace' => ['some/Namespace','some/Namespace'];
        yield 'some/empty namespace' => ['some//Namespace','some/Namespace'];
        yield 'encoded namespace' => ['some/Name%20space', 'some/Name space'];
        yield 'complex namespace' => ['/yet/another//Name%20space/', 'yet/another/Name space'];
    }

    public static function dpStringsToDecoded(): Generator {
        yield 'some string' => ['someString','someString'];
        yield 'encoded string' => ['some%20%22encoded%22%20string', 'some "encoded" string'];
    }

    /**
     * @psalm-return Generator<string, array{string, array<string, string>}>
     */
    public static function dpNormalizeQualifiers(): Generator
    {
        yield 'empty' => ['', null];
        yield 'some empty value' => ['k=', null];
        yield 'some kv' => ['k=v', ['k'=>'v']];
        yield 'some KV' => ['k=V', ['k'=>'V']];
        yield 'some encoded value' => ['k=a%20value', ['k'=>'a value']];
        yield 'checksums' => ['checksum=sha1:1234567890123,md5:4567890123456789012', ['checksum' => ['sha1:1234567890123', 'md5:4567890123456789012']]];
        yield 'multiple KVs' => ['k1=v1&k2=v2&k3=', ['k1'=>'v1', 'k2'=>'v2']];
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

    public static function dpStringsEmptyToNull(): Generator {
        yield 'empty' => ['', null];
    }

}
