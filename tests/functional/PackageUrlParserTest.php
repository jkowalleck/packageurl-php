<?php

namespace PackageUrl\Tests\functional;

use PackageUrl\PackageUrlParser;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 *
 * @psalm-import-type TDataSet from \PackageUrl\Tests\_data\TestSuiteData
 *
 * @author jkowalleck
 */
class PackageUrlParserTest extends TestCase
{
    /**
     * @dataProvider \PackageUrl\Tests\_data\TestSuiteData::data
     * @psalm-param TDataSet $data
     */
    public function test(array $data): void
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

}
