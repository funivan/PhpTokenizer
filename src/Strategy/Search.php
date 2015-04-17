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
    public function process(\Funivan\PhpTokenizer\Collection $collection, $currentIndex) {

      $result = new Result();

      # iterate while we can check toke

      $index = $currentIndex;
      do {

        $token = $collection->offsetGet($index);

        $index++;

        if ($this->isValid($token)) {
          $result->setNexTokenIndex($index);
          $result->setValid(true);
          $result->setToken($token);
          return $result;
        }

      } while (!empty($token));


      return $result;
    }

  }