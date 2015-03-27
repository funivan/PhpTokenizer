<?php

  namespace Funivan\PhpTokenizer\Query\QueryProcessor;

  /**
   *
   * @package Funivan\PhpTokenizer\BlockExtractor
   */
  class QueryProcessorResult {

    const STRATEGY_SOFT = 0;

    const STRATEGY_FORCE = 1;


    /**
     * @var null|int
     */
    protected $startIndex = null;

    /**
     * @var null|int
     */
    protected $endIndex = null;

    /**
     * @var null|int
     */
    protected $nextTokenIndexForCheck = null;

    /**
     * @var int
     */
    protected $startIndexStrategy;

    /**
     * @var int
     */
    protected $endIndexStrategy;

    /**
     * @param int $startIndex
     * @param null $strategy
     * @return $this
     */
    public function moveStartIndex($startIndex, $strategy = null) {
      if (!is_integer($startIndex)) {
        throw new \InvalidArgumentException("Expect integer for startIndex. Given:" . gettype($startIndex));
      }

      $this->startIndex = $startIndex;

      $this->startIndexStrategy = ($strategy !== null) ? $strategy : static::STRATEGY_SOFT;

      return $this;
    }

    /**
     * @param int $endIndex
     * @param null $strategy
     * @return $this
     */
    public function moveEndIndex($endIndex, $strategy = null) {
      if (!is_integer($endIndex)) {
        throw new \InvalidArgumentException("Expect integer for startIndex. Given:" . gettype($endIndex));
      }

      $this->endIndex = $endIndex;

      $this->endIndexStrategy = ($strategy !== null) ? $strategy : static::STRATEGY_SOFT;

      return $this;
    }

    /**
     * @param int|null $nextTokenIndexForCheck
     * @return $this
     */
    public function setNextTokenIndexForCheck($nextTokenIndexForCheck) {
      if (!is_integer($nextTokenIndexForCheck)) {
        throw new \InvalidArgumentException("Expect integer for nextTokenIndexForCheck. Given:" . gettype($nextTokenIndexForCheck));
      }

      $this->nextTokenIndexForCheck = $nextTokenIndexForCheck;

      return $this;
    }


    /**
     * @return null|int
     */
    public function getStartIndex() {
      return $this->startIndex;
    }

    /**
     * @return null|int
     */
    public function getEndIndex() {
      return $this->endIndex;
    }

    /**
     * @return int|null
     */
    public function getNextTokenIndexForCheck() {
      return $this->nextTokenIndexForCheck;
    }


    /**
     * @return bool
     */
    public function isValid() {
      return (null !== $this->nextTokenIndexForCheck);
    }

    /**
     * @return int
     */
    public function getStartIndexStrategy() {
      return $this->startIndexStrategy;
    }

    /**
     * @return int
     */
    public function getEndIndexStrategy() {
      return $this->endIndexStrategy;
    }

  }