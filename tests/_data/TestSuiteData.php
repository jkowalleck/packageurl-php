<?php

namespace PackageUrl\Tests\_data;

use Generator;

/**
 * @psalm-type TDataSet = array{
 *      description: string,
 *      purl: string,
 *      canonical_purl: string,
 *      type: string|null,
 *      namespace: string|null,
 *      name: string|null,
 *      version: string|null,
 *      qualifiers: string|null,
 *      subpath: string|null,
 *      is_invalid: bool
 *  }
 */
abstract class TestSuiteData {

    /**
     * data example
     *  - "description": "valid maven purl",
     *  - "purl": "pkg:maven/org.apache.commons/io@1.3.4",
     *  - "canonical_purl": "pkg:maven/org.apache.commons/io@1.3.4",
     *  - "type": "maven",
     *  - "namespace": "org.apache.commons",
     *  - "name": "io",
     *  - "version": "1.3.4",
     *  - "qualifiers": null,
     *  - "subpath": null,
     *  - "is_invalid": false
     *
     * @psalm-return Generator<string, array{TDataSet}>
     */
    public static function data(): Generator {
        $testSuite = json_decode(file_get_contents(__DIR__.'/test-suite-data.json'), true, 521, JSON_THROW_ON_ERROR);
        foreach ($testSuite as $data) {
            yield $data['description'] = [$data];
        }
    }

}
