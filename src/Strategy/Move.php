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
     * @return static
     */
    public static function create($direction, $steps) {
      return new static($direction, $steps);
    }

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
    public function process(\Funivan\PhpTokenizer\Collection $collection, $currentIndex) {
      $result = new Result();

      if ($this->isForward()) {
        $endIndex = $currentIndex + $this->steps;
      } else {
        $currentIndex--;
        $endIndex = $currentIndex - $this->steps;
      }

      $result->setNexTokenIndex($endIndex);
      if (isset($collection[$endIndex])) {
        $result->setValid(true);
        $result->setToken($collection[$endIndex]);
      } else {
        $result->setValid(false);
      }

      return $result;
    }

  }