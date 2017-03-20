<?php

  declare(strict_types=1);

  namespace Funivan\PhpTokenizer\QuerySequence;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Strategy\StrategyInterface;
  use Funivan\PhpTokenizer\Token;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 4/19/15
   */
  interface QuerySequenceInterface {

    /**
     * @param Collection $collection
     * @param $initialPosition
     */
    public function __construct(Collection $collection, $initialPosition);


    /**
     * @param StrategyInterface $strategy
     * @return Token
     */
    public function process(StrategyInterface $strategy);


    /**
     * @return Collection
     */
    public function getCollection();

  }