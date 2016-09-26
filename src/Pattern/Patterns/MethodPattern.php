<?php

  declare(strict_types = 1);

  namespace Funivan\PhpTokenizer\Pattern\Patterns;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Pattern\PatternMatcher;
  use Funivan\PhpTokenizer\QuerySequence\QuerySequence;
  use Funivan\PhpTokenizer\Strategy\QueryStrategy;
  use Funivan\PhpTokenizer\Strategy\Strict;
  use Funivan\PhpTokenizer\Token;

  /**
   * Find method in code
   */
  class MethodPattern implements PatternInterface {

    const OUTPUT_BODY = 0;

    const OUTPUT_FULL = 1;

    const OUTPUT_DOC_COMMENT = 2;


    /**
     * @var QueryStrategy
     */
    private $nameQuery;

    /**
     * @var callable[]
     */
    private $modifierChecker = [];

    /**
     * @var callable
     */
    private $bodyChecker;

    /**
     * @var callable
     */
    private $docCommentChecker;

    /**
     * @var int
     */
    private $outputType = self::OUTPUT_BODY;

    /**
     * @var ParametersPattern
     */
    private $argumentsPattern;


    /**
     *
     */
    public function __construct() {
      $this->withName(Strict::create()->valueLike('!.+!'));
      $this->withAnyModifier();

      /** @noinspection PhpUnusedParameterInspection */
      $this->withBody(function (Collection $body) {
        return true;
      });

      /** @noinspection PhpUnusedParameterInspection */
      $this->withDocComment(function (Token $token) {
        return true;
      });

    }


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
    public function outputBody() {
      $this->outputType = self::OUTPUT_BODY;
      return $this;
    }


    /**
     * @return $this
     */
    public function outputDocComment() {
      $this->outputType = self::OUTPUT_DOC_COMMENT;
      return $this;
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
     * @param callable $check
     * @return $this
     */
    public function withBody(callable $check) {
      $this->bodyChecker = $check;
      return $this;
    }


    /**
     * @param Collection $body
     * @return bool
     * @throws \Exception
     */
    private function isValidBody(Collection $body) {
      $checker = $this->bodyChecker;
      $result = $checker($body);
      if (!is_bool($result)) {
        throw new \Exception('Body checker should return boolean result');
      }

      return $result;
    }


    /**
     * @param callable $check
     * @return $this
     */
    public function withDocComment(callable $check = null) {

      if ($check === null) {
        $check = function (Token $token) {
          return $token->getType() === T_DOC_COMMENT;
        };
      }
      $this->docCommentChecker = $check;

      return $this;
    }


    /**
     * Find functions without doc comments
     */
    public function withoutDocComment() {
      $this->docCommentChecker = function (Token $token) {
        return $token->isValid() === false;
      };

      return $this;
    }


    /**
     * @param ParametersPattern $pattern
     * @return $this
     */
    public function withParameters(ParametersPattern $pattern) {
      $this->argumentsPattern = $pattern;
      return $this;
    }


    /**
     * @return $this
     */
    public function withAnyModifier() {
      $this->modifierChecker = [];
      $this->modifierChecker[] = function () {
        return true;
      };
      return $this;
    }


    /**
     * @param string $modifier
     * @return $this
     */
    public function withModifier($modifier) {
      $this->modifierChecker[] = function ($allModifiers) use ($modifier) {
        return in_array($modifier, $allModifiers);
      };

      return $this;
    }


    /**
     * @param string $modifier
     * @return $this
     */
    public function withoutModifier($modifier) {

      $this->modifierChecker[] = function ($allModifiers) use ($modifier) {
        return !in_array($modifier, $allModifiers);
      };

      return $this;
    }


    /**
     * @param array $modifiers Array<String>
     * @return bool
     * @throws \Exception
     */
    private function isValidModifiers(array $modifiers) {
      foreach ($this->modifierChecker as $checkModifier) {
        $result = $checkModifier($modifiers);

        if (!is_bool($result)) {
          throw new \Exception('Modifier checker should return boolean result');
        }
        if ($result === false) {
          return false;
        }
      }

      return true;
    }


    /**
     * @param QuerySequence $querySequence
     * @return Collection|null
     */
    public function __invoke(QuerySequence $querySequence) {
      static $availableModifiers = [
        T_STATIC,
        T_PRIVATE,
        T_PUBLIC,
        T_ABSTRACT,
        T_FINAL,
      ];


      # detect function
      $functionKeyword = $querySequence->strict('function');
      $querySequence->strict(T_WHITESPACE);
      $querySequence->process($this->nameQuery);
      $arguments = $querySequence->section('(', ')');
      $querySequence->possible(T_WHITESPACE);
      $body = $querySequence->section('{', '}');

      if (!$querySequence->isValid()) {
        return null;
      }

      $collection = $querySequence->getCollection();
      $start = $collection->extractByTokens($collection->getFirst(), $functionKeyword);
      $start->slice(0, -1);  // remove last function keyword

      # start reverse search
      $items = array_reverse($start->getTokens());
      $startFrom = null;

      $docComment = new Token();

      $modifiers = [];


      /** @var Token[] $items */
      foreach ($items as $item) {

        if ($item->getType() === T_WHITESPACE) {
          $startFrom = $item;
          continue;
        }

        if ($item->getType() === T_DOC_COMMENT and $docComment->isValid() === false) {
          # Detect only first doc comment
          $startFrom = $item;
          $docComment = $item;
          continue;
        }


        if (in_array($item->getType(), $availableModifiers)) {
          $startFrom = $item;
          $modifiers[] = $item->getValue();
          continue;
        }

        break;
      }

      if ($this->isValidModifiers($modifiers) === false) {
        return null;
      }

      if ($this->isValidBody($body) === false) {
        return null;
      }

      if ($this->isValidDocComment($docComment) === false) {
        return null;
      }

      if ($this->isValidArguments($arguments) === false) {
        return null;
      }

      if ($startFrom === null) {
        $startFrom = $functionKeyword;
      }


      if ($this->outputType === self::OUTPUT_FULL) {
        # all conditions are ok, so extract full function
        $fullFunction = $collection->extractByTokens($startFrom, $body->getLast());
        if ($fullFunction->getFirst()->getType() === T_WHITESPACE) {
          $fullFunction->slice(1);
        }
        return $fullFunction;

      } elseif ($this->outputType === self::OUTPUT_DOC_COMMENT) {
        return new Collection([$docComment]);
      }

      # body by default
      $body->slice(0, -1);
      return $body;
    }


    /**
     * @param Token $token
     * @return mixed
     * @throws \Exception
     */
    private function isValidDocComment(Token $token) {
      $checker = $this->docCommentChecker;
      $result = $checker($token);
      if (!is_bool($result)) {
        throw new \Exception('DocComment checker should return boolean result');
      }

      return $result;
    }


    /**
     * @param Collection $parameters
     * @return bool
     */
    private function isValidArguments(Collection $parameters) {
      if ($this->argumentsPattern === null) {
        return true;
      }

      $pattern = (new PatternMatcher($parameters))->apply($this->argumentsPattern);

      return (count($pattern->getCollections()) !== 0);
    }


  }