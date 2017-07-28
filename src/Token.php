<?php

  declare(strict_types=1);

  namespace Funivan\PhpTokenizer;

  use Funivan\PhpTokenizer\Exception\Exception;
  use Funivan\PhpTokenizer\Exception\InvalidArgumentException;

  /**
   *
   * Value is 2 type variable. It can be string or null
   * When you set value is automatically cast to string
   *
   *
   */
  class Token {

    const INVALID_TYPE = -1;

    const INVALID_LINE = -1;

    const INVALID_VALUE = null;

    const INVALID_INDEX = -1;

    /**
     * @var int
     */
    protected $type = self::INVALID_TYPE;

    /**
     * @var string|null
     */
    protected $value;

    /**
     * @var int
     */
    protected $line = self::INVALID_LINE;

    /**
     * Indicate position in current collection
     *
     * @var int
     */
    protected $index = self::INVALID_INDEX;


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
      return $this->assemble();
    }


    /**
     * @return string
     */
    public function assemble() : string {
      return $this->value !== null ? (string) $this->value : '';
    }


    /**
     * @param array $data
     * @return $this
     * @throws Exception
     */
    protected function setData(array $data) : self {
      if (!array_key_exists(0, $data)) {
        throw new InvalidArgumentException('Please provide type of token');
      }

      $this->setType((int) $data[0]);

      if (!isset($data[1])) {
        throw new InvalidArgumentException('Please provide value of token');
      }

      $this->setValue($data[1]);

      if (!array_key_exists(2, $data)) {
        throw new InvalidArgumentException('Please provide line of token');
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
    public function getData() : array {
      return [$this->getType(), $this->getValue(), $this->getLine(), $this->getIndex()];
    }


    /**
     * @param int $type
     * @return $this
     */
    public function setType(int $type) : self {
      $this->type = $type;
      return $this;
    }


    /**
     * @return int
     */
    public function getType() : int {
      return $this->type;
    }


    /**
     * @return string
     */
    public function getTypeName() : string {
      return token_name($this->getType());
    }


    /**
     * @return string|null
     */
    public function getValue() {
      return $this->value;
    }


    /**
     * @param string|int $value
     * @throws InvalidArgumentException
     * @return $this
     */
    public function setValue($value) : self {
      if (!is_string($value) and !is_numeric($value)) {
        throw new InvalidArgumentException('You can set only string. Given: ' . gettype($value));
      }
      $this->value = (string) $value;
      return $this;
    }


    /**
     * @return int
     */
    public function getLine() : int {
      return $this->line;
    }


    /**
     * @param int $line
     * @return $this
     */
    public function setLine(int $line) : self {
      $this->line = $line;
      return $this;
    }


    /**
     * @return bool
     */
    public function isValid() : bool {
      return $this->getValue() !== null;
    }


    /**
     * Remove all data from token so this token become invalid
     *
     * @return $this
     */
    public function remove() : self {
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
    public function appendToValue($part) : self {

      if (!is_string($part) and !is_numeric($part)) {
        throw new InvalidArgumentException('You can append only string to value');
      }

      $this->value .= $part;

      return $this;
    }


    /**
     * Add part to the begin of value
     *
     * @param string $part
     * @return $this
     * @throws Exception
     */
    public function prependToValue($part) : self {

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
     * @param int $index
     * @return $this
     */
    public function setIndex(int $index) : self {
      $this->index = $index;
      return $this;
    }


    public function equal(Token $token) : bool {
      return (
        $this->value === $token->getValue()
        and
        $this->type === $token->getType()
      );
    }

  }