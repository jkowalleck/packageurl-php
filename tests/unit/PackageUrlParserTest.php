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

        self::assertEquals($expectedOutput, $output);
    }


    /**
     * @dataProvider dpStringsToLowercase
     */
    public function testNormalizeScheme(string $input, string $expectedOutput): void
    {
        $parser = new PackageUrlParser();
        $normalized = $parser->normalizeScheme($input);
        self::assertEquals($expectedOutput, $normalized);
    }

    /**
     * @dataProvider dpStringsToLowercase
     */
    public function testNormalizeType(string $input, string $expectedOutput): void
    {
        $parser = new PackageUrlParser();
        $normalized = $parser->normalizeType($input);
        self::assertEquals($expectedOutput, $normalized);
    }


    /**
     * @dataProvider dpNormalizeNamespace
     */
    public function testNormalizeNamespace(string $input, string $expectedOutput): void
    {
        $parser = new PackageUrlParser();
        $normalized = $parser->normalizeNamespace($input);
        self::assertEquals($expectedOutput, $normalized);
    }

    /**
     * @dataProvider dpStringsToDecoded
     */
    public function testNormalizeName(string $input, string $expectedOutput): void
    {
        $parser = new PackageUrlParser();
        $normalized = $parser->normalizeName($input);
        self::assertEquals($expectedOutput, $normalized);
    }

    /**
     * @dataProvider dpStringsToDecoded
     */
    public function testNormalizeVersion(string $input, string $expectedOutput): void
    {
        $parser = new PackageUrlParser();
        $normalized = $parser->normalizeVersion($input);
        self::assertEquals($expectedOutput, $normalized);
    }

    /**
     * @dataProvider dpNormalizeSubpath
     */
    public function testNormalizeSubpath(string $input, string $expectedOutcome): void
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
        yield 'some namespace' => ['some/Namespace','some/Namespace'];
        yield 'encoded namespace' => ['some/Name%20space', 'some/Name space'];
    }

    public static function dpStringsToDecoded(): Generator {
        yield 'some string' => ['someString','someString'];
        yield 'encoded string' => ['some%20%22encoded%22%20string', 'some "encoded" string'];
    }


    /**
     * @psalm-return Generator<string, array{string, string}>
     */
    public static function dpNormalizeSubpath(): Generator
    {
        yield 'empty' => ['', ''];
        yield 'dot' => ['.', ''];
        yield 'dot dot' => ['..', ''];
        yield 'path' => ['path', 'path'];
        yield 'some/path' => ['some/path', 'some/path'];
        yield 'surrounding slashes' => ['/path//', 'path'];
        yield 'inner slashes' => ['some//path/', 'some/path'];
        yield 'encoded' => ['some%20path/', 'some path'];
        yield 'complex' => ['//foo/./bar/..//Baz%20ooF/', 'foo/bar/Baz ooF'];

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

}
