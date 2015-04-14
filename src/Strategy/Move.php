<?php

  namespace Funivan\PhpTokenizer\Strategy;

  /**
   *
   * @package Funivan\PhpTokenizer\BlockExtractor
   */
  class Move implements StrategyInterface {

    /**
     * Direction forward flag
     */
    const DIRECTION_FORWARD = 1;

    /**
     * Direction back flag
     */
    const DIRECTION_BACK = 2;

    /**
     * @var int
     */
    protected $steps = null;

    /**
     * @var int
     */
    protected $direction = null;


    /**
     * @param $direction
     * @param $steps
     */
    public function __construct($direction, $steps) {

      if (!is_integer($steps)) {
        throw new \InvalidArgumentException("Invalid steps. Expect integer. Given: " . gettype($steps));
      }

      $this->steps = $steps;

      if ($direction !== static::DIRECTION_BACK and $direction !== static::DIRECTION_FORWARD) {
        throw new \InvalidArgumentException("Invalid direction.");
      }

      $this->direction = $direction;
    }

    /**
     * @return bool
     */
    protected function isForward() {
      return ($this->direction === static::DIRECTION_FORWARD);
    }

    /**
     * @inheritdoc
     */
    public function getNextTokenIndex(\Funivan\PhpTokenizer\Collection $collection, $currentIndex) {
      $result = new \Funivan\PhpTokenizer\Strategy\QueryProcessorResult();

      if ($this->isForward()) {
        $endIndex = $currentIndex + $this->steps;
        $result->moveEndIndex($endIndex - 1, QueryProcessorResult::STRATEGY_FORCE);
        $result->setNextTokenIndexForCheck($endIndex);
      } else {
        $currentIndex--;

        $startIndex = $currentIndex - $this->steps;

        $result->moveEndIndex($startIndex, QueryProcessorResult::STRATEGY_FORCE);
        $result->setNextTokenIndexForCheck(($startIndex + 1));
      }

      return $result;
    }

  }