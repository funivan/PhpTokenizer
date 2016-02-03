<?php

  namespace Funivan\PhpTokenizer\Pattern\Patterns;

  use Funivan\PhpTokenizer\QuerySequence\QuerySequence;
  use Funivan\PhpTokenizer\Strategy\Possible;
  use Funivan\PhpTokenizer\Strategy\QueryStrategy;
  use Funivan\PhpTokenizer\Strategy\Strict;

  /**
   * Pattern used to finding classes in tour source code
   *
   */
  class ClassPattern implements PatternInterface {

    /**
     * Result of this pattern will be body of the class
     */
    const OUTPUT_BODY = 1;

    /**
     * Result of this pattern will be full class
     */
    const OUTPUT_FULL = 2;


    /**
     * @var QueryStrategy
     */
    private $nameQuery = null;

    /**
     * @var int
     */
    private $outputType = self::OUTPUT_BODY;


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
     * @return $this
     */
    public function outputBody() {
      $this->outputType = self::OUTPUT_BODY;
      return $this;
    }


    /**
     * @return $this
     */
    public function outputFull() {
      $this->outputType = self::OUTPUT_FULL;
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

      $start = $querySequence->strict('class');
      $querySequence->strict(T_WHITESPACE);
      $querySequence->process($this->nameQuery);
      $startClassBody = $querySequence->search('{');
      $querySequence->moveToToken($startClassBody);
      $body = $querySequence->section('{', '}');

      if ($start->isValid()) {
        # catch class modifiers
        $querySequence->moveToToken($start);
        $querySequence->move(-2);
        $modifier = $querySequence->process(Possible::create()->valueIs([
          'abstract',
          'final',
        ]));
        if ($modifier->isValid()) {
          $start = $modifier;
        }
      }

      if (!$querySequence->isValid()) {
        return null;
      }

      if ($this->outputType == self::OUTPUT_BODY) {
        return $body->extractItems(1, -1);
      }

      if ($this->outputType == self::OUTPUT_FULL) {
        return $querySequence->getCollection()->extractByTokens($start, $body->getLast());
      }

    }

  }