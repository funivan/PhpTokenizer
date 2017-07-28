<?php

  declare(strict_types=1);

  namespace Funivan\PhpTokenizer\Pattern\Patterns;

  use Funivan\PhpTokenizer\QuerySequence\QuerySequence;
  use Funivan\PhpTokenizer\Strategy\Possible;
  use Funivan\PhpTokenizer\Strategy\QueryStrategy;
  use Funivan\PhpTokenizer\Strategy\Strict;
  use Funivan\PhpTokenizer\Token;

  /**
   * PatternMatcher used to finding classes in tour source code
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
    private $nameQuery;

    /**
     * @var callable
     */
    private $docCommentChecker;

    /**
     * @var callable[]
     */
    private $modifierChecker;

    /**
     * @var int
     */
    private $outputType = self::OUTPUT_FULL;


    /**
     * By default we search for classes with any name
     */
    public function __construct() {
      $this->nameQuery = Strict::create()->valueLike('!.+!');
      $this->withPossibleDocComment();
      $this->withAnyModifier();
    }


    /**
     * @param QueryStrategy|string $name
     * @return $this
     */
    public function withName($name): self {
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
    public function withDocComment(): self {
      $this->docCommentChecker = function (Token $comment, QuerySequence $q) {
        if ($comment->getType() !== T_DOC_COMMENT) {
          $q->setValid(false);
        }
      };
      return $this;
    }


    /**
     * @return $this
     */
    public function withPossibleDocComment(): self {
      $this->docCommentChecker = function () {

      };
      return $this;
    }


    /**
     * @return $this
     */
    public function withoutDocComment(): self {
      $this->docCommentChecker = function (Token $comment, QuerySequence $q) {
        if ($comment->getType() === T_DOC_COMMENT) {
          $q->setValid(false);
        }
      };
      return $this;
    }


    /**
     * @return $this
     */
    public function outputBody(): self {
      $this->outputType = self::OUTPUT_BODY;
      return $this;
    }


    /**
     * @return $this
     */
    public function outputFull(): self {
      $this->outputType = self::OUTPUT_FULL;
      return $this;
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

      # self::OUTPUT_FULL
      $lastBodyToken = $body->getLast();
      if ($lastBodyToken === null) {
        return null;
      }

      if ($this->outputType === self::OUTPUT_BODY) {
        return $body->extractItems(1, -1);
      }


      return $querySequence->getCollection()->extractByTokens($start, $lastBodyToken);
    }


    /**
     * @return $this
     */
    public function withAnyModifier(): self {
      $this->modifierChecker = [];
      $this->modifierChecker[] = function () {
      };
      return $this;
    }


    /**
     * @param string $modifier
     * @return $this
     */
    public function withModifier(string $modifier): self {

      $this->modifierChecker[] = function (Token $token, QuerySequence $q) use ($modifier) {
        if ($token->getValue() !== $modifier) {
          $q->setValid(false);
        }
      };


      return $this;
    }


    /**
     * @param string $modifier
     * @return $this
     */
    public function withoutModifier(string $modifier): self {

      $this->modifierChecker[] = function (Token $token, QuerySequence $q) use ($modifier) {
        if ($token->getValue() === $modifier) {
          $q->setValid(false);
        }
      };
      return $this;
    }


  }