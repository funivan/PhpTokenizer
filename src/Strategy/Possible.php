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
    public function process(\Funivan\PhpTokenizer\Collection $collection, $currentIndex) {

      $result = new Result();
      $result->setValid(true);

      $token = $collection->offsetGet($currentIndex);

      if ($token and $this->isValid($token)) {
        $result->setToken($token);
        ++$currentIndex;
      }

      $result->setNexTokenIndex($currentIndex);

      return $result;
    }

  }