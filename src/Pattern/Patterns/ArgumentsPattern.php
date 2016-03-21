<?php

  namespace Funivan\PhpTokenizer\Pattern\Patterns;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\QuerySequence\QuerySequence;
  use Funivan\PhpTokenizer\Strategy\Section;
  use Funivan\PhpTokenizer\Token;

  /**
   *
   */
  class ArgumentsPattern implements PatternInterface {

    /**
     * @var array
     */
    private $argumentCheck = [];

    /**
     * @var int|null
     */
    private $outputArgument = null;


    /**
     *
     */
    public function __construct() {
      $this->outputFull();
    }


    /**
     * @param QuerySequence $querySequence
     * @return Collection|null
     * @throws \Exception
     */
    public function __invoke(QuerySequence $querySequence) {
      $section = $querySequence->section('(', ')');
      if ($section->count() === 0) {
        return null;
      }

      $section->slice(1, -1);

      if (empty($this->argumentCheck) and $this->outputArgument === null) {
        return $section;
      }


      /** @var Collection[] $arguments */
      $arguments = $this->getArguments($section);

      foreach ($this->argumentCheck as $index => $check) {

        $argumentTokens = isset($arguments[$index]) ? $arguments[$index] : new Collection();
        $result = $check($argumentTokens);

        if (!is_bool($result)) {
          throw new \Exception('Argument check function should return boolean');
        }

        if ($result === false) {
          return null;
        }
      }

      if ($this->outputArgument !== null) {
        $argumentCollection = !empty($arguments[$this->outputArgument]) ? $arguments[$this->outputArgument] : null;

        return $argumentCollection;
      }

      # output full
      return $section;
    }


    /**
     * @param int $int
     * @param callable $check
     * @return $this
     */
    public function withArgument($int, callable $check = null) {
      if ($check === null) {
        $check = function (Collection $argumentTokens) {
          return $argumentTokens->count() !== 0;
        };
      }
      $this->argumentCheck[$int] = $check;
      return $this;
    }


    /**
     * @param int $int
     * @return $this
     */
    public function withoutArgument($int) {
      $check = function (Collection $argumentTokens) {
        return $argumentTokens->count() === 0;
      };

      $this->argumentCheck[$int] = $check;
      return $this;
    }


    /**
     * @param $section
     * @return array
     */
    protected function getArguments(Collection $section) {
      /** @var Token $skipToToken */
      $skipToToken = null;
      $argumentIndex = 1;
      $arguments = [];
      $tokensNum = ($section->count() - 1);
      for ($tokenIndex = 0; $tokenIndex <= $tokensNum; $tokenIndex++) {

        $token = $section->offsetGet($tokenIndex);

        if ($token === null) {
          return null;
        }

        if ($skipToToken === null or $token->getIndex() >= $skipToToken->getIndex()) {
          if ($token->getValue() === ',') {
            $argumentIndex++;
            continue;
          }
          $skipToToken = $this->getEndArray($token, $section, $tokenIndex);
        }


        if (!isset($arguments[$argumentIndex])) {
          $arguments[$argumentIndex] = new Collection();
        }
        $arguments[$argumentIndex][] = $token;
      }

      return $arguments;
    }


    /**
     * @param Token $token
     * @param Collection $section
     * @param $index
     * @return Token
     */
    private function getEndArray(Token $token, Collection $section, $index) {
      // # check if we have array start

      if ($token->getValue() === '[') {
        $result = (new Section())->setDelimiters('[', ']')->process($section, $index);
        return $result->getToken();
      }

      if ($token->getValue() === '(') {
        $result = (new Section())->setDelimiters('(', ')')->process($section, $index);
        return $result->getToken();
      }

      return null;
    }


    /**
     * @return $this
     */
    public function outputFull() {
      $this->outputArgument = null;
      return $this;
    }


    /**
     * @param $int
     * @return $this
     */
    public function outputArgument($int) {
      $this->outputArgument = $int;
      return $this;
    }

  }