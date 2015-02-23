<?php

  namespace Funivan\PhpTokenizer;

  /**
   *
   * Value is 2 type variable. It can be string or null
   * When you set value is automatically cast to string
   *
   * @package Funivan\PhpTokenizer
   */
  class Token {

    const N = __CLASS__;

    const INVALID_TOKEN_TYPE = -1;

    const INVALID_TOKEN_LINE = -1;

    protected $type = null;

    protected $value = null;

    protected $line = null;

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
     * @return mixed
     */
    public function __toString() {
      return $this->value !== null ? (string) $this->value : '';
    }


    /**
     * @return bool
     */
    public function isValid() {
      return $this->getValue() !== null;
    }

    /**
     * @return array
     */
    public function getData() {
      return [$this->getType(), $this->getValue(), $this->getLine()];
    }

    /**
     * @return null
     */
    public function getType() {
      return $this->type;
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
     * @return string
     */
    public function getTypeName() {
      return token_name($this->getType());
    }

    /**
     * @return mixed
     */
    public function getValue() {
      return $this->value;
    }


    /**
     * @param string|int $value
     * @throws \Funivan\PhpTokenizer\Exception
     * @return $this
     */
    public function setValue($value) {

      if (!is_string($value) and !is_numeric($value)) {
        throw new \Funivan\PhpTokenizer\Exception('You can set only string ');
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
     * Remove all data from token so this token become invalid
     * @void
     */
    public function remove() {
      foreach ($this as $property => $value) {
        $this->$property = null;
      }
    }

    /**
     * @param array $data
     * @throws Exception
     */
    protected function setData(array $data) {
      if (!isset($data[0])) {
        throw new Exception("Please provide type of token");
      }

      $this->setType($data[0]);

      if (!isset($data[1])) {
        throw new Exception("Please provide value of token");
      }

      $this->setValue($data[1]);

      if (!isset($data[2])) {
        throw new Exception("Please provide line of token");
      }

      $this->setLine($data[2]);
    }

  }