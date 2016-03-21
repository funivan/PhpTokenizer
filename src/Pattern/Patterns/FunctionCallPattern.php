<?php

  namespace Funivan\PhpTokenizer\Pattern\Patterns;

  use Funivan\PhpTokenizer\Pattern\Pattern;
  use Funivan\PhpTokenizer\Query\Query;
  use Funivan\PhpTokenizer\QuerySequence\QuerySequence;

  /**
   *
   */
  class FunctionCallPattern implements PatternInterface {

    /**
     * @var Query|null
     */
    private $nameQuery;

    /**
     * @var ArgumentsPattern
     */
    private $parametersPattern;


    /**
     * @param Query $query
     * @return $this
     */
    public function withName(Query $query) {
      $this->nameQuery = $query;
      return $this;
    }


    /**
     * @param ArgumentsPattern $pattern
     * @return $this
     */
    public function withParameters(ArgumentsPattern $pattern) {
      $this->parametersPattern = $pattern;
      return $this;
    }


    /**
     * @inheritdoc
     */
    public function __invoke(QuerySequence $querySequence) {

      $name = $querySequence->strict(T_STRING);
      if ($this->nameQuery !== null and $this->nameQuery->isValid($name) === false) {
        return null;
      }

      $querySequence->possible(T_WHITESPACE);
      $arguments = $querySequence->section('(', ')');

      if (!$querySequence->isValid()) {
        return null;
      }

      $querySequence->moveToToken($name);
      $before = $querySequence->move(-1);
      if ($before->getType() === T_WHITESPACE) {
        $before = $querySequence->move(-1);
      }

      if (in_array($before->getValue(), ['::', 'function', '->'])) {
        return null;
      }

      if ($this->parametersPattern !== null) {
        $pattern = (new Pattern($arguments))->apply($this->parametersPattern);
        if (count($pattern->getCollections()) === 0) {
          return null;
        }
      }

      return $querySequence->getCollection()->extractByTokens($name, $arguments->getLast());
    }

  }