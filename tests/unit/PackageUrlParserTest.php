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
    public function dpTypeNamespaceNameVersion(): Generator {
        yield 'empty' => ['', ['','','','']];
        yield 'type' => ['someType', ['someType','','','']];
        yield 'type, name' => ['someType/someName', ['someType','','someName','']];
        yield 'type, name, version' => ['someType/someName@someVersion', ['someType','','someName','someVersion']];
        yield 'type, namespace, name' => ['someType/someNamespace/someName', ['someType','someNamespace','someName','']];
        yield 'type, name/space, name' => ['someType/some/Name/space/someName', ['someType','some/Name/space','someName','']];
        yield 'type, namespace, name, version' => ['someType/someNamespace/someName@someVersion', ['someType','someNamespace','someName','someVersion']];
    }
}
