<?php

  namespace Funivan\PhpTokenizer\Pattern\Patterns;

  use Funivan\PhpTokenizer\QuerySequence\QuerySequence;
  use Funivan\PhpTokenizer\Strategy\StrategyInterface;
  use Funivan\PhpTokenizer\Strategy\Strict;

  /**
   * 
   */
  class ClassPattern implements PatternInterface {

    /**
     * @var StrategyInterface
     */
    private $nameQuery = null;

    /**
     *
     */
    public function __construct() {
      $this->nameQuery = Strict::create()->valueLike('!.*!');
    }

    /**
     * @param string $name
     * @return $this
     */
    public function nameIs($name) {
      $this->nameQuery = Strict::create()->valueIs($name);
      return $this;
    }

    /**
     * @inheritdoc
     */
    public function __invoke(QuerySequence $querySequence) {
      $querySequence->setSkipWhitespaces(true);

      $querySequence->strict('class');
      $querySequence->process($this->nameQuery);
      $body = $querySequence->section('{', '}');

      if ($querySequence->isValid()) {
        return $body->extractItems(1, -1);
      }
    }

  }