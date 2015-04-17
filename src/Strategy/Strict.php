<?php

  namespace Funivan\PhpTokenizer\Strategy;

  use Funivan\PhpTokenizer\Query\Query;

  /**
   *
   * @package Funivan\PhpTokenizer\Query\Strategy
   */
  class Strict extends Query implements StrategyInterface {

    /**
     * @inheritdoc
     */
    public function process(\Funivan\PhpTokenizer\Collection $collection, $currentIndex) {

      $result = new Result();
      $result->setValid(true);

      $token = $collection->offsetGet($currentIndex);

      if ($token === null or $this->isValid($token) === false) {
        $result->setValid(false);
        return $result;
      }

      $result->setNexTokenIndex(++$currentIndex);
      $result->setToken($token);
      
      return $result;
    }

  }