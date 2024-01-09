<?php

declare(strict_types=1);

namespace Funivan\PhpTokenizer\Strategy;

use Funivan\PhpTokenizer\Collection;

interface StrategyInterface
{
    /**
     * Find next token for check
     * If this method return null we should stop check next tokens
     *
     * @param int $currentIndex
     * @return StrategyResult
     */
    public function process(Collection $collection, $currentIndex);
}
