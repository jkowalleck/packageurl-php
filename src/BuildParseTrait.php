<?php

namespace PackageUrl;

/**
 * @internal
 *
 * @author jkowaleck
 */
trait BuildParseTrait {

    private function isUsefulSubpathSegment (string $segment): bool {
        return false === in_array($segment, ['', '.', '..'], true);
    }
}
