<?php

  namespace Funivan\PhpTokenizer\Strategy;

  use Funivan\PhpTokenizer\Exception\InvalidArgumentException;

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
     * @param int $steps
     * @return static
     */
    public static function create($steps) {
      return new static($steps);
    }

    /**
     *
     * @param int $steps
     */
    public function __construct($steps) {

      if (!is_integer($steps)) {
        throw new InvalidArgumentException("Invalid steps. Expect integer. Given: " . gettype($steps));
      }

      $this->steps = $steps;

    }


    /**
     * @inheritdoc
     */
    public function process(\Funivan\PhpTokenizer\Collection $collection, $currentIndex) {
      $result = new Result();

      $endIndex = $currentIndex + $this->steps;

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