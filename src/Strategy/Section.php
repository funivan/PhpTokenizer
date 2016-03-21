<?php

  namespace Funivan\PhpTokenizer\Strategy;

  use Funivan\PhpTokenizer\Exception\InvalidArgumentException;
  use Funivan\PhpTokenizer\Query\Query;
  use Funivan\PhpTokenizer\Query\QueryInterface;

  /**
   *
   * @package Funivan\PhpTokenizer\Query\Strategy
   */
  class Section extends QueryStrategy {

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
     * @inheritdoc
     */
    public function process(\Funivan\PhpTokenizer\Collection $collection, $currentIndex) {

      $this->requireQueries();

      $result = new StrategyResult();
      $token = $collection->offsetGet($currentIndex);
      if (empty($token) or $this->startQuery->isValid($token) === false) {
        return $result;
      }

      $blockEndFlag = null;
      $startIndex = null;
      $endIndex = null;
      foreach ($collection as $tokenIndex => $token) {
        if ($tokenIndex < $currentIndex) {
          continue;
        }

        if ($this->startQuery->isValid($token)) {
          $blockEndFlag++;
          if ($blockEndFlag === 1) {
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

      if ($startIndex !== null and $endIndex !== null) {
        $result = new StrategyResult();
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


    protected function requireQueries() {
      if (empty($this->startQuery)) {
        throw new InvalidArgumentException('Empty start Query. ');
      }

      if (empty($this->endQuery)) {
        throw new InvalidArgumentException('Empty end Query. ');
      }
    }

  }