<?php

  namespace Funivan\PhpTokenizer;

  use Funivan\PhpTokenizer\Exception\Exception;
  use Funivan\PhpTokenizer\Query\Query;

  /**
   * Represent access and manipulation array of tokens
   *
   * @method \Funivan\PhpTokenizer\Token getLast();
   * @method \Funivan\PhpTokenizer\Token current();
   * @method \Funivan\PhpTokenizer\Token|null offsetGet($index);
   * @method \Funivan\PhpTokenizer\Token|null getFirst();
   * @method \Funivan\PhpTokenizer\Token[] getItems();
   * @method \Funivan\PhpTokenizer\Collection extractItems($offset, $length = null);
   * @method $this setItems($tokens)
   *
   * @package Funivan\PhpTokenizer
   */
  class Collection extends \Fiv\Collection\ObjectCollection {

    /**
     * You can use this constant for access class name
     */
    const N = __CLASS__;

    /**
     * @var string
     */
    protected $initialContentHash;


    /**
     * @param array $items
     */
    public function __construct(array $items = array()) {
      parent::__construct($items);
      $this->storeContentHash();
    }


    /**
     * Extract each value from token
     *
     * @return string
     */
    public function __toString() {
      return $this->assemble();
    }


    /**
     *
     * @param string $string
     * @return Collection
     * @throws Exception
     */
    public static function createFromString($string) {
      $tokens = Helper::getTokensFromString($string);
      return new static($tokens);
    }


    /**
     * @return bool
     */
    public function isChanged() {
      return ($this->getContentHash() !== $this->initialContentHash);
    }


    /**
     * @return string
     */
    private function getContentHash() {
      return md5($this->assemble());
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
     * @codeCoverageIgnore
     * @deprecated
     * @see createFromString
     */
    public static function initFromString($string) {
      trigger_error(__CLASS__ . '::' . __METHOD__ . ' deprecated and will be removed in 0.1.3 Use ' . __CLASS__ . '::createFromString', E_USER_DEPRECATED);
      return self::createFromString($string);
    }


    /**
     * Remove invalid tokens from collection
     *
     * @return $this
     */
    private function cleanCollection() {
      foreach ($this as $index => $token) {
        if ($token->isValid()) {
          continue;
        }
        unset($this->items[$index]);
      }

      return $this;
    }


    /**
     * Remove all tokens in collection
     *
     * @return $this
     */

    public function remove() {
      foreach ($this as $token) {
        $token->remove();
      }
      return $this;
    }


    /**
     * @param Token $tokenStart
     * @param Token $tokenEnd
     * @return Collection
     */
    public function extractByTokens(Token $tokenStart, Token $tokenEnd) {

      $collection = new Collection();
      $startIndex = $tokenStart->getIndex();
      $endIndex = $tokenEnd->getIndex();

      foreach ($this->getItems() as $token) {
        if ($token->getIndex() >= $startIndex and $token->getIndex() <= $endIndex) {
          $collection->append($token);
        }
      }


      return $collection;
    }


    /**
     * @param Query $query
     * @return Collection
     */
    public function find(Query $query) {
      $finder = new TokenFinder($this);
      return $finder->find($query);
    }


    /**
     * @return $this
     */
    public function storeContentHash() {
      $this->initialContentHash = $this->getContentHash();
      return $this;
    }

  }
