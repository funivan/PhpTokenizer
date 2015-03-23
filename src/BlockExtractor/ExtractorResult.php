<?php

  namespace Funivan\PhpTokenizer\BlockExtractor;

  /**
   *
   * @package Funivan\PhpTokenizer\BlockExtractor
   */
  class ExtractorResult {

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
     * @param int|null $startIndex
     * @return $this
     */
    public function setStartIndex($startIndex) {
      if (!is_integer($startIndex)) {
        throw new \InvalidArgumentException("Expect integer for startIndex. Given:" . gettype($startIndex));
      }
      $this->startIndex = $startIndex;
      
      return $this;
    }

    /**
     * @param int|null $endIndex
     * @return $this
     */
    public function setEndIndex($endIndex) {
      if (!is_integer($endIndex)) {
        throw new \InvalidArgumentException("Expect integer for startIndex. Given:" . gettype($endIndex));
      }
      $this->endIndex = $endIndex;
      
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

  }