<?php

  namespace Funivan\PhpTokenizer;

  use Funivan\PhpTokenizer\Query\Query;

  /**
   * Represent access and manipulation array of tokens
   *
   * @method \Funivan\PhpTokenizer\Token getLast();
   * @method \Funivan\PhpTokenizer\Token current();
   * @method \Funivan\PhpTokenizer\Token offsetGet($index);
   * @method \Funivan\PhpTokenizer\Token getFirst();
   * @method \Funivan\PhpTokenizer\Token[] getItems();
   * @method \Funivan\PhpTokenizer\Collection extractItems($offset, $length = null);
   * @method $this setItems($tokens)
   *
   * @package Funivan\PhpTokenizer
   */
  class Collection extends \Fiv\Collection\ObjectCollection {

    const N = __CLASS__;

    /**
     * Extract each value from token
     *
     * @return string
     */
    public function __toString() {
      return $this->assemble();
    }

    /**
     * Used for validation
     *
     * @return string
     */
    public function objectsClassName() {
      return Token::N;
    }

    /**
     * @return string
     */
    public function assemble() {
      $string = '';
      /** @var Token $token */
      foreach ($this as $token) {
        if (!$token->isValid()) {
          continue;
        }
        $string .= $token->getValue();
      }

      return $string;
    }

    /**
     * Remove all invalid tokens in collection
     * Refresh index.
     *
     * @return $this
     */
    public function refresh() {
      $string = $this->assemble();
      $this->cleanCollection();

      $tokens = Helper::getTokensFromString($string);
      $this->setItems($tokens);

      $this->rewind();
      return $this;
    }

    /**
     * @param int $step
     * @return Token
     */
    public function getPrevious($step = 1) {
      $item = parent::getPrevious($step);
      if ($item === null) {
        $item = new Token();
      }
      return $item;
    }

    /**
     * @param int $step
     * @return Token
     */
    public function getNext($step = 1) {
      $item = parent::getNext($step);
      if ($item === null) {
        $item = new Token();
      }
      return $item;
    }

    /**
     * @return Query
     */
    public function query() {
      return new Query($this);
    }

    /**
     * @return string
     */
    public function getDumpString() {
      $string = "<pre>\n";
      foreach ($this as $token) {
        $string .= '[' . $token->getTypeName() . ']' . "\n" . print_r($token->getData(), true) . "\n";
      }
      $string .= " </pre > ";
      return $string;
    }

    /**
     *
     * @param string $string
     * @return Collection
     * @throws Exception
     */
    public static function initFromString($string) {
      $tokens = Helper::getTokensFromString($string);
      return new Collection($tokens);
    }

    /**
     * Remove invalid tokens from collection
     *
     * @return $this
     */
    protected function cleanCollection() {
      foreach ($this as $index => $token) {
        if ($token->isValid()) {
          continue;
        }
        unset($this->items[$index]);
      }

      return $this;
    }

  }

