<?php

  namespace Funivan\PhpTokenizer\Strategy;

  use Funivan\PhpTokenizer\Query\Query;

  /**
   *
   * @package Funivan\PhpTokenizer\Query\Strategy
   */
  class Search extends Query implements StrategyInterface {

    /**
     * @inheritdoc
     */
    public function getNextTokenIndex(\Funivan\PhpTokenizer\Collection $collection, $currentIndex) {

      # iterate while we can check toke

      $index = $currentIndex;
      do {

        $token = $collection->offsetGet($index);

        $index++;

        if ($this->isValid($token)) {
          return $index;
        }

      } while (!empty($token));


      return null;
    }

  }