<?php

declare(strict_types=1);

namespace Funivan\PhpTokenizer\Pattern\Patterns;

use Funivan\PhpTokenizer\Collection;
use Funivan\PhpTokenizer\QuerySequence\QuerySequence;

interface PatternInterface
{
    /**
     * @return Collection|null
     */
    public function __invoke(QuerySequence $querySequence);
}
