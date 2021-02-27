<?php

declare(strict_types=1);

/*
 * Copyright (c) the purl authors
 * SPDX-License-Identifier: MIT
 * MIT License
 *
 * Copyright (c) 2021 package-url
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * Visit https://github.com/package-url/packageurl-php
 * for support and download.
 */

namespace PackageUrl\Tests;

use Generator;
use PackageUrl\PackageUrlParser;
use PHPUnit\Framework\TestCase;

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
    /** @var PackageUrlParser */
    private $sut;

    public function setUp(): void
    {
        $this->sut = new PackageUrlParser();
    }

    /**
     * @dataProvider dpStringsToLowercase
     * @dataProvider dpStringsEmptyAndNull
     */
    public function testNormalizeScheme(?string $input, ?string $expectedOutput): void
    {
        $normalized = $this->sut->normalizeScheme($input);
        self::assertSame($expectedOutput, $normalized);
    }

    /**
     * @dataProvider dpStringsToLowercase
     * @dataProvider dpStringsEmptyAndNull
     */
    public function testNormalizeType(?string $input, ?string $expectedOutput): void
    {
        $normalized = $this->sut->normalizeType($input);
        self::assertSame($expectedOutput, $normalized);
    }

    /**
     * @dataProvider dpNormalizeNamespace
     * @dataProvider dpNormalizeNamespaceSpecials
     * @dataProvider dpStringsEmptyAndNull
     *
     * @psalm-param non-empty-string|null $type
     */
    public function testNormalizeNamespace(?string $input, ?string $expectedOutput, ?string $type = null): void
    {
        $normalized = $this->sut->normalizeNamespace($input, $type);
        self::assertSame($expectedOutput, $normalized);
    }

    /**
     * @dataProvider dpNormalizeNameSpecials
     * @dataProvider dpStringsToDecoded
     * @dataProvider dpStringsEmptyAndNull
     *
     * @psalm-param non-empty-string|null $type
     */
    public function testNormalizeName(?string $input, ?string $expectedOutput, ?string $type = null): void
    {
        $normalized = $this->sut->normalizeName($input, $type);
        self::assertSame($expectedOutput, $normalized);
    }

    /**
     * @dataProvider dpStringsToDecoded
     * @dataProvider dpStringsEmptyAndNull
     */
    public function testNormalizeVersion(?string $input, ?string $expectedOutput): void
    {
        $normalized = $this->sut->normalizeVersion($input);
        self::assertSame($expectedOutput, $normalized);
    }

    /**
     * @dataProvider dpNormalizeQualifiers
     * @dataProvider dpStringsEmptyAndNull
     */
    public function testNormalizeQualifiers(?string $input, ?array $expectedOutcome): void
    {
        $normalized = $this->sut->normalizeQualifiers($input);
        self::assertSame($expectedOutcome, $normalized);
    }

    /**
     * @dataProvider dpNormalizeSubpath
     * @dataProvider dpStringsEmptyAndNull
     */
    public function testNormalizeSubpath(?string $input, ?string $expectedOutcome): void
    {
        $decoded = $this->sut->normalizeSubpath($input);
        self::assertSame($expectedOutcome, $decoded);
    }

    /**
     * @dataProvider \PackageUrl\Tests\_data\TestSuiteData::data
     * @psalm-param TDataSet $data
     */
    public function testParseAndNormalize(array $data): void
    {
        $expected = [
            'type' => $data['type'],
            'namespace' => $data['namespace'],
            'name' => $data['name'],
            'version' => $data['version'],
            'qualifiers' => $data['qualifiers'],
            'subpath' => $data['subpath'],
        ];

        $parsed = $this->sut->parse($data['purl']);
        $normalized = [
            'type' => $this->sut->normalizeType($parsed['type']),
            'namespace' => $this->sut->normalizeNamespace($parsed['namespace'], $parsed['type']),
            'name' => $this->sut->normalizeName($parsed['name'], $parsed['type']),
            'version' => $this->sut->normalizeVersion($parsed['version']),
            'qualifiers' => $this->sut->normalizeQualifiers($parsed['qualifiers']),
            'subpath' => $this->sut->normalizeSubpath($parsed['subpath']),
        ];

        if ($data['is_invalid']) {
            self::assertNotSame($expected, $normalized);
        } else {
            self::assertSame($expected, $normalized);
        }
    }

    /**
     * @psalm-return Generator<non-empty-string, array{string, string}>
     */
    public static function dpNormalizeNamespace(): Generator
    {
        yield 'empty/empty' => ['/', null];
        yield 'some Namespace' => ['some/Namespace', 'some/Namespace'];
        yield 'some/empty Namespace' => ['some//Namespace', 'some/Namespace'];
        yield 'encoded Namespace' => ['some/Name%20space', 'some/Name space'];
        yield 'complex Namespace' => ['/yet/another//Name%20space/', 'yet/another/Name space'];
    }

    /**
     * based on {@link https://github.com/package-url/purl-spec#known-purl-types Known purl types}.
     *
     * @psalm-return Generator<non-empty-string, array{string, string, non-empty-string}>
     */
    public static function dpNormalizeNamespaceSpecials(): Generator
    {
        yield 'bitbucket: lowercase' => ['FoO', 'foo', 'bitbucket'];
        yield 'deb: lowercase' => ['FoO', 'foo', 'deb'];
        yield 'github: lowercase' => ['FoO', 'foo', 'github'];
        yield 'golang: lowercase' => ['FoO', 'foo', 'golang'];
        yield 'hex: lowercase' => ['FoO', 'foo', 'hex'];
        yield 'rpm: lowercase' => ['FoO', 'foo', 'rpm'];
    }

    /**
     * based on {@link https://github.com/package-url/purl-spec#known-purl-types Known purl types}.
     *
     * @psalm-return Generator<non-empty-string, array{string, string, non-empty-string}>
     */
    public static function dpNormalizeNameSpecials(): Generator
    {
        yield 'bitbucket: lowercase' => ['FoO', 'foo', 'bitbucket'];
        yield 'deb: lowercase' => ['FoO', 'foo', 'deb'];
        yield 'github: lowercase' => ['FoO', 'foo', 'github'];
        yield 'golang: lowercase' => ['FoO', 'foo', 'golang'];
        yield 'hex: lowercase' => ['FoO', 'foo', 'hex'];
        yield 'pypi: lowercase' => ['FoO', 'foo', 'pypi'];
        yield 'pypi: underscores' => ['foo_bar', 'foo-bar', 'pypi'];
    }

    /**
     * @psalm-return Generator<non-empty-string, array{string, array<string, string>}>
     */
    public static function dpStringsToDecoded(): Generator
    {
        yield 'some string' => ['someString', 'someString'];
        yield 'encoded string' => ['some%20%22encoded%22%20string', 'some "encoded" string'];
    }

    /**
     * @psalm-return Generator<non-empty-string, array{string, array<string, string>}>
     */
    public static function dpNormalizeQualifiers(): Generator
    {
        yield 'some empty value' => ['k=', null];
        yield 'some none value' => ['k', null];
        yield 'some kv' => ['k=v', ['k' => 'v']];
        yield 'some KV' => ['k=V', ['k' => 'V']];
        yield 'some encoded value' => ['k=a%20value', ['k' => 'a value']];
        yield 'multiple KVs' => ['k1=v1&k2=v2&k3=&k4', ['k1' => 'v1', 'k2' => 'v2']];
    }

    /**
     * @psalm-return Generator<non-empty-string, array{string, string}>
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
     * @psalm-return Generator<non-empty-string, array{non-empty-string, non-empty-string}>
     */
    public static function dpStringsToLowercase(): Generator
    {
        yield 'lowercase' => ['something', 'something'];
        yield 'UPPERCASE' => ['SOMETHING', 'something'];
        yield 'mIxeDCase' => ['sOmetHIng', 'something'];
    }

    /**
     * @psalm-return Generator<non-empty-string, array{string|null, string|null}>
     */
    public static function dpStringsEmptyAndNull(): Generator
    {
        yield 'empty' => ['', null];
        yield 'null' => [null, null];
    }
}
