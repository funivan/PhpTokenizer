<?php

  namespace Funivan\PhpTokenizer\Pattern\Patterns;

  use Funivan\PhpTokenizer\Pattern\PatternMatcher;
  use Funivan\PhpTokenizer\Query\Query;
  use Funivan\PhpTokenizer\QuerySequence\QuerySequence;

  /**
   *
   */
  class FunctionCallPattern implements PatternInterface {

    const OUTPUT_FULL = 1;

    const OUTPUT_ARGUMENTS = 2;

    /**
     * @var Query|null
     */
    private $nameQuery;

    /**
     * @var ParametersPattern
     */
    private $parametersPattern;

    /**
     * @var int
     */
    private $outputType = self::OUTPUT_FULL;


    /**
     * @return $this
     */
    public function outputFull() {
      $this->outputType = self::OUTPUT_FULL;
      return $this;
    }


    /**
     * @return $this
     */
    public function outputArguments() {
      $this->outputType = self::OUTPUT_ARGUMENTS;
      return $this;
    }


    /**
     * @param Query $query
     * @return $this
     */
    public function withName(Query $query) {
      $this->nameQuery = $query;
      return $this;
    }


    /**
     * @param ParametersPattern $pattern
     * @return $this
     */
    public function withParameters(ParametersPattern $pattern) {
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
        $pattern = (new PatternMatcher($arguments))->apply($this->parametersPattern);
        if (count($pattern->getCollections()) === 0) {
          return null;
        }
      }

      if ($this->outputType === self::OUTPUT_ARGUMENTS) {
        return $arguments;
      }

      return $querySequence->getCollection()->extractByTokens($name, $arguments->getLast());
    }

  }