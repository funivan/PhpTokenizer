<?php

  namespace Funivan\PhpTokenizer;

  use Funivan\PhpTokenizer\Exception\Exception;
  use Funivan\PhpTokenizer\Exception\InvalidArgumentException;

  /**
   *
   * Value is 2 type variable. It can be string or null
   * When you set value is automatically cast to string
   *
   * @package Funivan\PhpTokenizer
   */
  class Token {

    const N = __CLASS__;

    const INVALID_TYPE = -1;

    const INVALID_LINE = -1;

    const INVALID_VALUE = null;

    const INVALID_INDEX = -1;

    /**
     * @var null|int
     */
    protected $type = null;

    /**
     * @var string|null
     */
    protected $value = null;

    /**
     * @var int|null
     */
    protected $line = null;

    /**
     * Indicate position in current collection
     *
     * @var null|int
     */
    protected $index = null;

    /**
     * You need to provide at least 3 elements
     *
     * @param array $data
     * @throws Exception
     */
    public function __construct(array $data = []) {
      if (!empty($data)) {
        $this->setData($data);
      }
    }

    /**
     * @return string
     */
    public function __toString() {
      return $this->value !== null ? (string) $this->value : '';
    }

    /**
     * @return string
     */
    public function assemble() {
      return $this->__toString();
    }

    /**
     * @param array $data
     * @return $this
     * @throws Exception
     */
    protected function setData(array $data) {
      if (!array_key_exists(0, $data)) {
        throw new InvalidArgumentException("Please provide type of token");
      }

      $this->setType($data[0]);

      if (!isset($data[1])) {
        throw new InvalidArgumentException("Please provide value of token");
      }

      $this->setValue($data[1]);

      if (!array_key_exists(2, $data)) {
        throw new InvalidArgumentException("Please provide line of token");
      }

      $this->setLine($data[2]);

      if (array_key_exists(3, $data)) {
        $this->setIndex($data[3]);
      }

      return $this;
    }

    /**
     * @return array
     */
    public function getData() {
      return [$this->getType(), $this->getValue(), $this->getLine(), $this->getIndex()];
    }


    /**
     * @param $type
     * @return $this
     */
    public function setType($type) {
      $this->type = $type;
      return $this;
    }

    /**
     * @return null|integer
     */
    public function getType() {
      return $this->type;
    }

    /**
     * @return string
     */
    public function getTypeName() {
      return token_name($this->getType());
    }

    /**
     * @return string
     */
    public function getValue() {
      return $this->value;
    }


    /**
     * @param string|int $value
     * @throws \Funivan\PhpTokenizer\Exception\Exception
     * @return $this
     */
    public function setValue($value) {

      if (!is_string($value) and !is_numeric($value)) {
        throw new Exception('You can set only string. Given: ' . gettype($value));
      }

      $this->value = (string) $value;
      return $this;
    }

    /**
     * @return int
     */
    public function getLine() {
      return $this->line;
    }

    /**
     * @param int $line
     * @return $this
     */
    public function setLine($line) {
      $this->line = $line;
      return $this;
    }

    /**
     * @return bool
     */
    public function isValid() {
      return $this->getValue() !== null;
    }

    /**
     * Remove all data from token so this token become invalid
     *
     * @return $this
     */
    public function remove() {
      $this->type = static::INVALID_TYPE;
      $this->value = static::INVALID_VALUE;
      $this->line = static::INVALID_LINE;
      $this->index = static::INVALID_INDEX;
      return $this;
    }


    /**
     * Add part to the end of value
     *
     * @param string $part
     * @return $this
     * @throws Exception
     */
    public function appendToValue($part) {

      if (!is_string($part) and !is_numeric($part)) {
        throw new InvalidArgumentException('You can append only string to value');
      }

      $this->value = $this->value . $part;
      
      return $this;
    }

    /**
     * Add part to the begin of value
     *
     * @param string $part
     * @return $this
     * @throws Exception
     */
    public function prependToValue($part) {
      
      if (!is_string($part) and !is_numeric($part)) {
        throw new InvalidArgumentException('You can prepend only string to value');
      }

      $this->value = $part . $this->value;

      return $this;
    }

    /**
     * @return null|int
     */
    public function getIndex() {
      return $this->index;
    }

    /**
     * @param null|int $index
     * @return $this
     */
    public function setIndex($index) {
      if ($index !== null and !is_int($index)) {
        throw new \InvalidArgumentException("Invalid position argument. Expect null or integer. Given #" . gettype($index));
      }

      $this->index = $index;
      return $this;
    }

  }