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
class PackageUrlParserTest extends TestCase {

    // region parseTypeNamespaceNameVersion

    /**
     * @dataProvider dpTypeNamespaceNameVersion
     *
     * @psalm-param TParsedTypeNamespaceNameVersion $expectedOutput
     */
    public function testParseTypeNamespaceNameVersion(string $input, array $expectedOutput): void {
        $parser = new PackageUrlParser();

        /** @see PackageUrlParser::parseTypeNamespaceNameVersion() */
        $parseTypeNamespaceNameVersion = (new ReflectionClass($parser))->getMethod('parseTypeNamespaceNameVersion');
        $parseTypeNamespaceNameVersion->setAccessible(true);

        $output = $parseTypeNamespaceNameVersion->invoke($parser, $input);

        self::assertEquals($expectedOutput, $output);
    }

    /**
     * @psalm-return Generator<string, array{string, TParsedTypeNamespaceNameVersion}>
     */
    public static function dpTypeNamespaceNameVersion(): Generator {
        yield 'empty' => ['', ['','','','']];
        yield 'type' => ['someType', ['someType','','','']];
        yield 'type, name' => ['someType/someName', ['someType','','someName','']];
        yield 'type, name, version' => ['someType/someName@someVersion', ['someType','','someName','someVersion']];
        yield 'type, namespace, name' => ['someType/someNamespace/someName', ['someType','someNamespace','someName','']];
        yield 'type, name/space, name' => ['someType/some/Name/space/someName', ['someType','some/Name/space','someName','']];
        yield 'type, namespace, name, version' => ['someType/someNamespace/someName@someVersion', ['someType','someNamespace','someName','someVersion']];
    }

    // endregion parseTypeNamespaceNameVersion

    // region urldecodePath


    /**
     * @dataProvider dpUrldecodePath
     */
    public function testUrldecodePath(string $input, string $expectedOutcome): void
    {
        $parser = new PackageUrlParser();

        $decoded = $parser->normalizePath($input);

        self::assertSame($expectedOutcome, $decoded);
    }

    /**
     * @psalm-return Generator<string, array{string, string}>
     */
    public static function dpUrldecodePath(): Generator {
        yield 'empty' => ['', ''];
        yield 'dot' => ['.', ''];
        yield 'dot dot' => ['..', ''];
        yield 'path' => ['path','path'];
        yield 'some/path' => ['some/path','some/path'];
        yield 'surrounding slashes' => ['/path//', 'path'];
        yield 'inner slashes' => ['some//path/', 'some/path'];
        yield 'encoded' => ['some%20path/', 'some path'];
        yield 'complex' => ['//foo/./bar/..//baz%20oof/', 'foo/bar/baz oof'];

    }

    //endregion urldecodePath

}
