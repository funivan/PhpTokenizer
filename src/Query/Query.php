<?php


  namespace Funivan\PhpTokenizer\Query;

  use Funivan\PhpTokenizer\Exception\InvalidArgumentException;
  use Funivan\PhpTokenizer\Token;

  /**
   * @author Ivan Shcherbak <dev@funivan.com>
   */
  class Query implements QueryInterface {

    const N = __CLASS__;

    /**
     * Constant indicate conditions IS equal to values
     */
    const IS = 'is';

    /**
     * Constant indicate conditions NOT equal to values
     */
    const NOT = 'no';

    const GREATER_THAN = 'gt';

    const LESS_THAN = 'lt';

    const LESS_THAN_EQUAL = 'lteq';

    const REXEG = 'regex';


    /**
     * Storage of type conditions
     *
     * @var array
     */
    protected $type = array();

    /**
     * Storage of conditions conditions
     *
     * @var array
     */
    protected $value = array();

    /**
     * Storage of line conditions
     *
     * @var array
     */
    protected $line = array();

    /**
     * Storage of index conditions
     *
     * @var array
     */
    protected $index = array();

    /**
     * @return static
     */
    public static function create() {
      return new static();
    }


    /**
     * @param int|array $type Array<Int>|Int
     * @return $this
     */
    public function typeIs($type) {
      $types = $this->prepareIntValues($type);
      $this->type[static::IS] = $types;
      return $this;
    }

    /**
     * @param int|array $type Array<Int>|Int
     * @return $this
     */
    public function typeNot($type) {
      $types = $this->prepareIntValues($type);
      $this->type[static::NOT] = $types;
      return $this;
    }

    /**
     * @param string $value Array<String>|String
     * @return $this
     */
    public function valueIs($value) {
      $value = $this->prepareValues($value);
      $this->value[self::IS] = $value;
      return $this;
    }

    /**
     * @param string $value Array<String>|String
     * @return $this
     */
    public function valueNot($value) {
      $value = $this->prepareValues($value);
      $this->value[self::NOT] = $value;
      return $this;
    }

    /**
     * @param string $regexp Array<String>|String
     * @return $this
     */
    public function valueLike($regexp) {
      $regexp = $this->prepareValues($regexp);
      $this->value[self::REXEG] = $regexp;
      return $this;
    }


    /**
     * @param int $line
     * @return $this
     */
    public function lineIs($line) {
      $lineNumbers = $this->prepareIntValues($line);
      $this->line[self::IS] = $lineNumbers;
      return $this;
    }

    /**
     * @param int $line
     * @return $this
     */
    public function lineNot($line) {
      $lineNumbers = $this->prepareIntValues($line);
      $this->line[self::NOT] = $lineNumbers;
      return $this;
    }

    /**
     * @param int $line
     * @return $this
     */
    public function lineGt($line) {
      $lineNumbers = $this->prepareIntValues($line);
      $this->line[self::GREATER_THAN] = max($lineNumbers);
      return $this;
    }

    /**
     * @param int $line
     * @return $this
     */
    public function lineLt($line) {
      $lineNumbers = $this->prepareIntValues($line);
      $this->line[self::LESS_THAN] = min($lineNumbers);
      return $this;
    }

    /**
     * @param int|int[] $index
     * @return $this
     */
    public function indexIs($index) {
      $indexNumbers = $this->prepareIntValues($index);
      $this->index[self::IS] = $indexNumbers;
      return $this;
    }

    /**
     * @param int|int[] $index
     * @return $this
     */
    public function indexNot($index) {
      $indexNumbers = $this->prepareIntValues($index);
      $this->index[self::NOT] = $indexNumbers;
      return $this;
    }

    /**
     * @param int|int[] $index
     * @return $this
     */
    public function indexGt($index) {
      $indexNumbers = $this->prepareIntValues($index);
      $this->index[self::GREATER_THAN] = max($indexNumbers);
      return $this;
    }

    /**
     * @param int|int[] $index
     * @return $this
     */
    public function indexLt($index) {
      $indexNumbers = $this->prepareIntValues($index);
      $this->index[self::LESS_THAN] = min($indexNumbers);
      return $this;
    }


    /**
     * @inheritdoc
     */
    public function isValid(\Funivan\PhpTokenizer\Token $token) {

      if (!$this->validateType($token)) {
        return false;
      }

      if (!$this->validateValue($token)) {
        return false;
      }

      if (!$this->validateIndex($token)) {
        return false;
      }


      return true;
    }

    /**
     * @param Token $token
     * @return bool
     */
    private function validateType($token) {

      if (empty($this->type)) {
        return true;
      }

      if (!$this->validateIsCondition($this->type, $token->getType())) {
        return false;
      }

      if (!$this->validateNotCondition($this->type, $token->getType())) {
        return false;
      }

      return true;
    }

    /**
     * @param Token $token
     * @return bool
     */
    private function validateIndex(Token $token) {

      if (empty($this->index)) {
        return true;
      }

      # check token index
      if (!$this->validateIsCondition($this->index, $token->getIndex())) {
        return false;
      }

      if (!$this->validateNotCondition($this->index, $token->getIndex())) {
        return false;
      }

      if (array_key_exists(static::GREATER_THAN, $this->index) and $token->getIndex() <= $this->index[static::GREATER_THAN]) {
        return false;
      }

      if (array_key_exists(static::LESS_THAN, $this->index) and $token->getIndex() >= $this->index[static::LESS_THAN]) {
        return false;
      }

      return true;
    }

    /**
     * @param Token $token
     * @return bool
     */
    private function validateValue(Token $token) {

      if (empty($this->value)) {
        return true;
      }

      $value = $token->getValue();

      # check line
      if (!$this->validateIsCondition($this->value, $value)) {
        return false;
      }

      if (!$this->validateNotCondition($this->value, $value)) {
        return false;
      }

      if (!$this->validateRegexpCondition($this->value, $value)) {
        return false;
      }

      return true;
    }

    /**
     * @param string|int|array $value String|Int|Array<String>|Array<Int>
     * @return array Array<String>
     * @throws \Exception
     */
    protected function prepareValues($value) {

      if ($value == null) {
        return array($value);
      }

      if (is_object($value)) {
        throw new InvalidArgumentException('Invalid conditions. Must be string or array of string');
      }

      $value = array_values((array) $value);

      foreach ($value as $k => $val) {
        if (!is_string($val) and !is_numeric($val)) {
          throw new InvalidArgumentException('Invalid conditions. Must be string');
        }

        $value[$k] = (string) $val;
      }
      return $value;
    }

    /**
     * @param array|int $value Array<Int>|Int
     * @return array
     * @throws \Exception
     */
    protected function prepareIntValues($value) {

      if ($value === null) {
        return array($value);
      }

      if (is_object($value)) {
        throw new InvalidArgumentException('Invalid condition value. Must be int. Object given');
      }

      $value = array_values((array) $value);


      foreach ($value as $intValue) {
        if (!is_int($intValue)) {
          throw new InvalidArgumentException('Invalid conditions. Must be integer. Given:' . gettype($intValue));
        }
      }
      return $value;
    }

    /**
     * @param $conditions
     * @param $value
     * @return bool
     */
    private function validateIsCondition($conditions, $value) {
      return (!isset($conditions[static::IS]) or in_array($value, $conditions[static::IS], true));
    }

    /**
     * @param $conditions
     * @param $line
     * @return bool
     */
    private function validateNotCondition($conditions, $line) {
      return (!isset($conditions[static::NOT]) or !in_array($line, $conditions[static::NOT], true));
    }

    /**
     * @param array $conditions
     * @param string $value
     * @return bool
     */
    private function validateRegexpCondition($conditions, $value) {

      # check conditions regexp
      if (empty($conditions[self::REXEG])) {
        return true;
      }

      foreach ($conditions[self::REXEG] as $regex) {
        if (!preg_match($regex, $value)) {
          return false;
        }
      }

      return true;
    }


  }