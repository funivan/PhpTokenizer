<?php

  namespace Funivan\PhpTokenizer\Pattern\Patterns;

  use Funivan\PhpTokenizer\QuerySequence\QuerySequence;
  use Funivan\PhpTokenizer\Strategy\QueryStrategy;
  use Funivan\PhpTokenizer\Strategy\Strict;

  /**
   * Class pattern used to finding classes in tour source code
   * This class find only class definition
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
     * @param string $name
     * @return $this
     */
    public function nameIs($name) {
      if (is_string($name)) {
        $this->whereName(Strict::create()->valueIs($name));
      } elseif ($name instanceof QueryStrategy) {
        $this->whereName($name);
      } else {
        throw new \InvalidArgumentException('Expect string or QueryInterface');
      }

      return $this;
    }

    /**
     * @param QueryStrategy $strategy
     */
    public function whereName(QueryStrategy $strategy) {
      $this->nameQuery = $strategy;
    }

    /**
     * @inheritdoc
     */
    public function __invoke(QuerySequence $querySequence) {

      $querySequence->strict('class');
      $querySequence->strict(T_WHITESPACE);
      $querySequence->process($this->nameQuery);
      $body = $querySequence->section('{', '}');

      if ($querySequence->isValid()) {
        return $body->extractItems(1, -1);
      }

      return null;
    }

  }