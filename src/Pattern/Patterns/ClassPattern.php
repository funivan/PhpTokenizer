<?php

  namespace Funivan\PhpTokenizer\Pattern\Patterns;

  use Funivan\PhpTokenizer\Query\QueryInterface;
  use Funivan\PhpTokenizer\QuerySequence\QuerySequence;
  use Funivan\PhpTokenizer\Strategy\StrategyInterface;
  use Funivan\PhpTokenizer\Strategy\Strict;

  /**
   * Class pattern used to finding classes in tour source code
   * This class find only class definition
   */
  class ClassPattern implements PatternInterface {

    /**
     * @var QueryInterface
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
      } elseif ($name instanceof QueryInterface) {
        $this->whereName($name);
      } else {
        throw new \InvalidArgumentException('Expect string or QueryInterface');
      }

      return $this;
    }

    /**
     * @param QueryInterface $strategy
     */
    public function whereName(QueryInterface $strategy) {
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
    }

  }