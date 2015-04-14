<?php

  namespace Funivan\PhpTokenizer\Strategy;

  use Funivan\PhpTokenizer\Query\Query;

  /**
   *
   * @package Funivan\PhpTokenizer\Query\Strategy
   */
  class Possible extends Query implements StrategyInterface {

    /**
     * @inheritdoc
     */
    public function getNextTokenIndex(\Funivan\PhpTokenizer\Collection $collection, $currentIndex) {

      $token = $collection->offsetGet($currentIndex);

      if ($this->isValid($token) == false) {
        return $currentIndex;
      }

      return ++$currentIndex;
    }

  }