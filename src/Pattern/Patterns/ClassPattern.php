<?php

  namespace Funivan\PhpTokenizer\Pattern\Patterns;

  use Funivan\PhpTokenizer\QuerySequence\QuerySequence;
  use Funivan\PhpTokenizer\Strategy\Possible;
  use Funivan\PhpTokenizer\Strategy\QueryStrategy;
  use Funivan\PhpTokenizer\Strategy\Strict;
  use Funivan\PhpTokenizer\Token;

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
     * @var callable
     */
    private $docCommentChecker;

    /**
     * @var callable
     */
    private $modifierChecker;

    /**
     * @var int
     */
    private $outputType = self::OUTPUT_BODY;


    /**
     * By default we search for classes with any name
     */
    public function __construct() {
      $this->nameQuery = Strict::create()->valueLike('!.+!');
      $this->withPossibleDocComment();
      $this->withAnyModifier();
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
    public function withDocComment() {
      $this->docCommentChecker = function (Token $comment, QuerySequence $q) {
        if ($comment->getType() != T_DOC_COMMENT) {
          $q->setValid(false);
        }
      };
      return $this;
    }


    /**
     * @return $this
     */
    public function withPossibleDocComment() {
      $this->docCommentChecker = function (Token $comment, QuerySequence $q) {
        return;
      };
      return $this;
    }


    /**
     * @return $this
     */
    public function withoutDocComment() {
      $this->docCommentChecker = function (Token $comment, QuerySequence $q) {
        if ($comment->getType() == T_DOC_COMMENT) {
          $q->setValid(false);
        }
      };
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


      $comment = $querySequence->process(Possible::create()->typeIs(T_DOC_COMMENT));

      $querySequence->possible(T_WHITESPACE);
      $modifier = $querySequence->process(Possible::create()->valueIs([
        'abstract',
        'final',
      ]));

      $querySequence->possible(T_WHITESPACE);
      $start = $querySequence->strict('class');
      $querySequence->strict(T_WHITESPACE);
      $querySequence->process($this->nameQuery);
      $startClassBody = $querySequence->search('{');
      $querySequence->moveToToken($startClassBody);
      $body = $querySequence->section('{', '}');

      if ($modifier->isValid()) {
        $start = $modifier;
      }

      if ($comment->isValid()) {
        $start = $comment;
      }

      $docCommentChecker = $this->docCommentChecker;
      $docCommentChecker($comment, $querySequence);


      foreach ($this->modifierChecker as $checker) {
        $checker($modifier, $querySequence);
      }


      if (!$querySequence->isValid()) {
        return null;
      }


      if ($this->outputType == self::OUTPUT_BODY) {
        return $body->extractItems(1, -1);
      }

      # self::OUTPUT_FULL
      return $querySequence->getCollection()->extractByTokens($start, $body->getLast());
    }


    /**
     * @return $this
     */
    public function withAnyModifier() {
      $this->modifierChecker = [];
      $this->modifierChecker[] = function (Token $token, QuerySequence $q) {
        return;
      };
      return $this;
    }


    /**
     * @param string $modifier
     * @return $this
     */
    public function withModifier($modifier) {

      $this->modifierChecker[] = function (Token $token, QuerySequence $q) use ($modifier) {
        if ($token->getValue() != $modifier) {
          $q->setValid(false);
        }
        return;
      };


      return $this;
    }


    /**
     * @param string $modifier
     * @return $this
     */
    public function withoutModifier($modifier) {

      $this->modifierChecker[] = function (Token $token, QuerySequence $q) use ($modifier) {
        if ($token->getValue() == $modifier) {
          $q->setValid(false);
        }
        return;
      };
      return $this;
    }


  }