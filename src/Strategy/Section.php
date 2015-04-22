<?php

  namespace Funivan\PhpTokenizer\Strategy;

  use Funivan\PhpTokenizer\Exception\InvalidArgumentException;
  use Funivan\PhpTokenizer\Query\Query;
  use Funivan\PhpTokenizer\Query\QueryInterface;

  /**
   *
   * @package Funivan\PhpTokenizer\Query\Strategy
   */
  class Section extends Query implements StrategyInterface {

    /**
     * @var QueryInterface
     */
    private $startQuery;

    /**
     * @var QueryInterface
     */
    private $endQuery;

    /**
     * @param string $start
     * @param string $end
     * @return $this
     */
    public function setDelimiters($start, $end) {
      $startQuery = new Query();
      $startQuery->valueIs($start);
      $this->setStartQuery($startQuery);

      $endQuery = new Query();
      $endQuery->valueIs($end);
      $this->setEndQuery($endQuery);

      return $this;
    }

    /**
     * @param \Funivan\PhpTokenizer\Collection $collection
     * @param int $currentIndex
     * @return int|null
     */
    public function process(\Funivan\PhpTokenizer\Collection $collection, $currentIndex) {

      if (empty($this->startQuery)) {
        throw new InvalidArgumentException("Empty start Query. ");
      }

      if (empty($this->endQuery)) {
        throw new InvalidArgumentException("Empty end Query. ");
      }

      $result = new Result();
      $token = $collection->offsetGet($currentIndex);
      if (empty($token) or $this->startQuery->isValid($token) === false) {
        return $result;
      }

      $blockEndFlag = null;
      $startIndex = null;

      foreach ($collection as $tokenIndex => $token) {

        if ($tokenIndex < $currentIndex) {
          continue;
        }

        if ($this->startQuery->isValid($token)) {
          $blockEndFlag++;
          if ($blockEndFlag == 1) {
            $startIndex = $tokenIndex;
          }

        } elseif ($startIndex !== null and $this->endQuery->isValid($token)) {
          $blockEndFlag--;
        }

        if ($blockEndFlag === 0) {
          $endIndex = $tokenIndex;
          break;
        }
      }

      if (isset($startIndex) and isset($endIndex)) {
        $result = new Result();
        $result->setValid(true);
        $result->setNexTokenIndex(++$endIndex);
        $result->setToken($token);
      }

      return $result;
    }

    /**
     * @param QueryInterface $startQuery
     * @return $this
     */
    public function setStartQuery(QueryInterface $startQuery) {
      $this->startQuery = $startQuery;
      return $this;
    }

    /**
     * @param QueryInterface $endQuery
     * @return $this
     */
    public function setEndQuery(QueryInterface $endQuery) {
      $this->endQuery = $endQuery;
      return $this;
    }

  }