<?php

declare(strict_types=1);

namespace Funivan\PhpTokenizer\Pattern;

use Exception;
use Funivan\PhpTokenizer\Collection;

/**
 * Check collection according to callback patterns
 */
interface PatternMatcherInterface
{
    /**
     * @return $this
     * @throws Exception
     */
    public function apply(callable $pattern);

    /**
     * @return Collection[]
     */
    public function getCollections();
}
