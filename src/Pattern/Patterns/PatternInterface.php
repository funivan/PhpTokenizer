<?php

  declare(strict_types = 1);

  namespace Funivan\PhpTokenizer\Pattern\Patterns;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\QuerySequence\QuerySequence;

  /**
   * @package Funivan\PhpTokenizer\PatternMatcher\Patterns
   */
  interface PatternInterface {

    /**
     * @param QuerySequence $querySequence
     * @return Collection|null
     */
    public function __invoke(QuerySequence $querySequence);

  }