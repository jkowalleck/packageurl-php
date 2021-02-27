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

namespace PackageUrl\Tests\_data;

use Generator;
use PackageUrl\PackageUrl;

abstract class PackageUrlProvider
{
    public static function parserSpecials(): Generator
    {
        yield 'schemaDoubleSlash' => [
            'pkg://gem/ruby-advisory-db-check@0.12.4',
            (
            new PackageUrl('gem', 'ruby-advisory-db-check'))
                ->setVersion('0.12.4'),
        ];
    }

    /**
     * examples taken from
     * {@link https://github.com/package-url/purl-spec/blob/master/README.rst#some-purl-examples}.
     *
     * @see https://github.com/package-url/purl-spec/blob/master/README.rst#purl
     * format: "scheme:type/namespace/name@version?qualifiers#subpath"
     *
     * @psalm-return Generator<string, array{PackageUrl}>
     */
    public static function examples(): Generator
    {
        $examples = [
            'pkg:bitbucket/birkenfeld/pygments-main@244fd47e07d1014f0aed9c' => (
            new PackageUrl('bitbucket', 'pygments-main'))
                ->setNamespace('birkenfeld')
                ->setVersion('244fd47e07d1014f0aed9c'),

            'pkg:deb/debian/curl@7.50.3-1?arch=i386&distro=jessie' => (
            new PackageUrl('deb', 'curl'))
                ->setNamespace('debian')
                ->setVersion('7.50.3-1')
                ->setQualifiers(['arch' => 'i386', 'distro' => 'jessie']),

            'pkg:docker/cassandra@sha256:244fd47e07d1004f0aed9c' => (
            new PackageUrl('docker', 'cassandra'))
                ->setVersion('sha256:244fd47e07d1004f0aed9c'),

            'pkg:docker/customer/dockerimage@sha256:244fd47e07d1004f0aed9c?repository_url=gcr.io' => (
            new PackageUrl('docker', 'dockerimage'))
                ->setNamespace('customer')
                ->setVersion('sha256:244fd47e07d1004f0aed9c')
                ->setQualifiers(['repository_url' => 'gcr.io']),

            'pkg:gem/jruby-launcher@1.1.2?platform=java' => (
            new PackageUrl('gem', 'jruby-launcher'))
                ->setVersion('1.1.2')
                ->setQualifiers(['platform' => 'java']),

            'pkg:gem/ruby-advisory-db-check@0.12.4' => (
            new PackageUrl('gem', 'ruby-advisory-db-check'))
                ->setVersion('0.12.4'),

            'pkg:github/package-url/purl-spec@244fd47e07d1004f0aed9c' => (
            new PackageUrl('github', 'purl-spec'))
                ->setNamespace('package-url')
                ->setVersion('244fd47e07d1004f0aed9c'),

            'pkg:golang/google.golang.org/genproto' => (
            new PackageUrl('golang', 'genproto'))
                ->setNamespace('google.golang.org'),

            'pkg:maven/org.apache.xmlgraphics/batik-anim@1.9.1?packaging=sources' => (
            new PackageUrl('maven', 'batik-anim'))
                ->setNamespace('org.apache.xmlgraphics')
                ->setVersion('1.9.1')
                ->setQualifiers(['packaging' => 'sources']),

            'pkg:maven/org.apache.xmlgraphics/batik-anim@1.9.1?repository_url=repo.spring.io/release' => (
            new PackageUrl('maven', 'batik-anim'))
                ->setNamespace('org.apache.xmlgraphics')
                ->setVersion('1.9.1')
                ->setQualifiers(['repository_url' => 'repo.spring.io/release']),

            'pkg:npm/%40angular/animation@12.3.1' => (
            new PackageUrl('npm', 'animation'))
                ->setNamespace('@angular')
                ->setVersion('12.3.1'),

            'pkg:npm/foobar@12.3.1' => (
            new PackageUrl('npm', 'foobar'))
                ->setVersion('12.3.1'),

            'pkg:nuget/EnterpriseLibrary.Common@6.0.1304' => (
            new PackageUrl('nuget', 'EnterpriseLibrary.Common'))
                ->setVersion('6.0.1304'),

            'pkg:pypi/django@1.11.1' => (
            new PackageUrl('pypi', 'django'))
                ->setVersion('1.11.1'),

            'pkg:rpm/fedora/curl@7.50.3-1.fc25?arch=i386&distro=fedora-25' => (
            new PackageUrl('rpm', 'curl'))
                ->setNamespace('fedora')
                ->setVersion('7.50.3-1.fc25')
                ->setQualifiers(['arch' => 'i386', 'distro' => 'fedora-25']),

            'pkg:rpm/opensuse/curl@7.56.1-1.1.?arch=i386&distro=opensuse-tumbleweed' => (
            new PackageUrl('rpm', 'curl'))
                ->setNamespace('opensuse')
                ->setVersion('7.56.1-1.1.')
                ->setQualifiers(['arch' => 'i386', 'distro' => 'opensuse-tumbleweed']),
        ];

        foreach ($examples as $string => $object) {
            yield $string => [$string, $object];
        }
    }
}
