<?php

  declare(strict_types=1);

  namespace Funivan\PhpTokenizer\Strategy;

  use Funivan\PhpTokenizer\Exception\InvalidArgumentException;

  /**
   *
   *
   */
  class Search extends QueryStrategy {

    /**
     * Move forward flag
     *
     * @var int
     */
    const FORWARD = 1;

    /**
     * Move backward flag
     *
     * @var int
     */
    const BACKWARD = -1;

    /**
     * @var int
     */
    protected $direction = 1;


    /**
     * @inheritdoc
     */
    public function process(\Funivan\PhpTokenizer\Collection $collection, $currentIndex) {

      $result = new StrategyResult();

      # getProcessor while we can check toke

      $index = $currentIndex;
      $searchForward = ($this->direction === static::FORWARD);

      do {

        $token = $collection->offsetGet($index);
        if ($token === null) {
          return $result;
        }
        $index = $searchForward ? ++$index : --$index;

        if ($this->isValid($token)) {
          $result->setNexTokenIndex($index);
          $result->setValid(true);
          $result->setToken($token);
          break;
        }

      } while (!empty($token));

      return $result;
    }


    /**
     * @param int $direction
     * @return $this
     */
    public function setDirection($direction) {

      if ($direction !== static::FORWARD and $direction !== static::BACKWARD) {
        throw new InvalidArgumentException('Invalid direction option');
      }

      $this->direction = $direction;
      return $this;
    }

  }