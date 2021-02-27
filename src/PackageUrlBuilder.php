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

namespace PackageUrl;

/**
 * @psalm-import-type TType from PackageUrl
 * @psalm-import-type TNamespace from PackageUrl
 * @psalm-import-type TName from PackageUrl
 * @psalm-import-type TVersion from PackageUrl
 * @psalm-import-type TQualifiers from PackageUrl
 * @psalm-import-type TSubpath from PackageUrl
 *
 * @author jkowalleck
 */
class PackageUrlBuilder
{
    /**
     * @psalm-param non-empty-string $scheme
     * @psalm-param TType $type
     * @psalm-param TNamespace $namespace
     * @psalm-param TName $name
     * @psalm-param TVersion $version
     * @psalm-param TQualifiers $qualifiers
     * @psalm-param TSubpath $subpath
     *
     * @psalm-return non-empty-string
     *
     * @TODO see specs & implement
     */
    public function build(string $scheme, string $type, ?string $namespace, string $name, ?string $version, ?array $qualifiers, ?string $subpath): string
    {
        return $scheme.
        ':'.$type.
        (null === $namespace ? '' : '/'.rawurlencode($namespace)).
        '/'.rawurlencode($name).
        (null === $version ? '' : '@'.rawurlencode($version)).
        (null === $qualifiers || 0 === count($qualifiers) ? '' : '?'.http_build_query($qualifiers)).
        (null === $subpath ? '' : '#'.$subpath);
    }
}
