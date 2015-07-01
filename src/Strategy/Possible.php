<?php

  namespace Funivan\PhpTokenizer\Strategy;


  /**
   *
   * @package Funivan\PhpTokenizer\Query\Strategy
   */
  class Possible extends BaseStrategy {

    /**
     * @inheritdoc
     */
    public function process(\Funivan\PhpTokenizer\Collection $collection, $currentIndex) {

      $result = new StrategyResult();
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