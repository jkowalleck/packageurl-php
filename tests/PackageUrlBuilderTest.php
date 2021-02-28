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
use PackageUrl\PackageUrl;
use PackageUrl\PackageUrlBuilder;
use PackageUrl\Tests\_data\TestSuiteData;
use PHPUnit\Framework\TestCase;
use DomainException;

/**
 * @covers \PackageUrl\PackageUrlBuilder
 *
 * @psalm-import-type TDataSet from \PackageUrl\Tests\_data\TestSuiteData
 *
 * @author jkowalleck
 */
class PackageUrlBuilderTest extends TestCase
{
    /** @var PackageUrlBuilder */
    private $sut;

    public function setUp(): void
    {
        $this->sut = new PackageUrlBuilder();
    }

    /**
     * @dataProvider dpValidTestData
     * @psalm-param TDataSet $data
     */
    public function testBuild(array $data): void
    {
        $expected = $data['canonical_purl'];
        $built = $this->sut->build(
            $data['type'],
            $data['namespace'],
            $data['name'],
            $data['version'],
            $data['qualifiers'],
            $data['subpath'],
        );

        self::assertEquals($expected, $built);

    }

    /**
     * @psalm-return Generator<non-empty-string, array{TDataSet}>
     */
    public static function dpValidTestData(): Generator
    {
        foreach (TestSuiteData::data() as $label => [$data]) {
            if (true === $data['is_invalid']) {
                continue;
            }
            yield $label => [$data];
        }
    }

}
