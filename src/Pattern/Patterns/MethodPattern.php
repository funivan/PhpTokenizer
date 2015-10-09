<?php

  namespace Funivan\PhpTokenizer\Pattern\Patterns;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\QuerySequence\QuerySequence;
  use Funivan\PhpTokenizer\Strategy\QueryStrategy;
  use Funivan\PhpTokenizer\Strategy\Strict;

  /**
   *
   * @package Atl\Automation
   */
  class MethodPattern implements PatternInterface {

    /**
     * @var QueryStrategy|null
     */
    private $nameQuery;


    /**
     * MethodPattern constructor.
     */
    public function __construct() {
      $this->nameQuery = Strict::create()->valueLike('!.+!');
    }


    /**
     * @param string|QueryStrategy $name
     * @return $this
     */
    public function withName($name) {
      if (is_string($name)) {
        $this->nameQuery = Strict::create()->valueIs($name);
      } elseif ($name instanceof QueryStrategy) {
        $this->nameQuery = $name;
      } else {
        throw new \InvalidArgumentException('Invalid name format. Expect string or Query');
      }

      return $this;
    }


    /**
     * @param QuerySequence $querySequence
     * @return Collection|null
     */
    public function __invoke(QuerySequence $querySequence) {
      
      $querySequence->strict('function');
      $querySequence->strict(T_WHITESPACE);
      $querySequence->process($this->nameQuery);
      $querySequence->section('(', ')');
      $body = $querySequence->section('{', '}');

      if ($querySequence->isValid()) {
        return $body->extractItems(1, -1);
      }
      
      return null;
    }

  }