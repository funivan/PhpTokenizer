<?php

  namespace Funivan\PhpTokenizer\Pattern\Patterns;

  use Funivan\PhpTokenizer\QuerySequence\QuerySequence;
  use Funivan\PhpTokenizer\Strategy\QueryStrategy;
  use Funivan\PhpTokenizer\Strategy\Strict;

  /**
   * Pattern used to finding classes in tour source code
   *
   */
  class ClassPattern implements PatternInterface {

    /**
     * @var QueryStrategy
     */
    private $nameQuery = null;


    /**
     * By default we search for classes with any name
     */
    public function __construct() {
      $this->nameQuery = Strict::create()->valueLike('!.+!');
    }


    /**
     * @codeCoverageIgnore
     * @deprecated
     * @param string $name
     * @return $this
     */
    public function nameIs($name) {
      trigger_error("Deprecated. Use withName", E_USER_DEPRECATED);
      return $this->withName($name);
    }


    /**
     * @param QueryStrategy|string $name
     * @return $this
     */
    public function withName($name) {
      if (is_string($name)) {
        $this->nameQuery = Strict::create()->valueIs($name);
      } elseif ($name instanceof QueryStrategy) {
        $this->nameQuery = $name;
      } else {
        throw new \InvalidArgumentException('Expect string or QueryInterface');
      }

      return $this;
    }


    /**
     * @codeCoverageIgnore
     * @deprecated
     * @param QueryStrategy $strategy
     * @return $this
     */
    public function whereName(QueryStrategy $strategy) {
      trigger_error("Deprecated. Use withName", E_USER_DEPRECATED);
      return $this->withName($strategy);
    }


    /**
     * @inheritdoc
     */
    public function __invoke(QuerySequence $querySequence) {

      $querySequence->strict('class');
      $querySequence->strict(T_WHITESPACE);
      $querySequence->process($this->nameQuery);
      $startClassBody = $querySequence->search('{');
      $querySequence->moveToToken($startClassBody);
      $body = $querySequence->section('{', '}');

      if ($querySequence->isValid()) {
        return $body->extractItems(1, -1);
      }

      return null;
    }

  }