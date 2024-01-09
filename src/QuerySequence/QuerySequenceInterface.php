<?php

declare(strict_types=1);

namespace Funivan\PhpTokenizer\QuerySequence;

use Funivan\PhpTokenizer\Collection;
use Funivan\PhpTokenizer\Strategy\StrategyInterface;
use Funivan\PhpTokenizer\Token;

interface QuerySequenceInterface
{
    public function __construct(Collection $collection, $initialPosition);

    /**
     * @return Token
     */
    public function process(StrategyInterface $strategy);

    /**
     * @return Collection
     */
    public function getCollection();
}
