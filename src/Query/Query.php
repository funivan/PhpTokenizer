<?php


  namespace Funivan\PhpTokenizer\Query;

  use Funivan\PhpTokenizer\Exception\Exception;
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

    const FIELD_VALUE = 'value';

    const FIELD_TYPE = 'type';

    const FIELD_LINE = 'line';

    const FIELD_INDEX = 'index';

    /**
     * Storage of type conditions
     *
     * @var array
     */
    protected $type = array();

    /**
     * Storage of value conditions
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
     * @param int|array $type
     * @return $this
     */
    public function typeIs($type) {
      return $this->addCondition(self::FIELD_TYPE, $type, self::IS);
    }

    /**
     * @param int|array $type
     * @return $this
     */
    public function typeNot($type) {
      return $this->addCondition(self::FIELD_TYPE, $type, self::NOT);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function valueIs($value) {
      $value = $this->prepareValues($value);
      return $this->addCondition(self::FIELD_VALUE, $value, self::IS);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function valueNot($value) {
      $value = $this->prepareValues($value);
      return $this->addCondition(self::FIELD_VALUE, $value, self::NOT);
    }

    /**
     * @param string $regexp
     * @return $this
     */
    public function valueLike($regexp) {
      $regexp = $this->prepareValues($regexp);
      return $this->addCondition(self::FIELD_VALUE, $regexp, self::REXEG);
    }


    /**
     * @param int $lineNumber
     * @return $this
     */
    public function lineIs($lineNumber) {
      return $this->addCondition(self::FIELD_LINE, $lineNumber, self::IS);
    }

    /**
     * @param int $lineNumber
     * @return $this
     */
    public function lineNot($lineNumber) {
      return $this->addCondition(self::FIELD_LINE, $lineNumber, self::NOT);
    }

    /**
     * @param int $lineNumber
     * @return $this
     */
    public function lineGt($lineNumber) {
      return $this->addCondition(self::FIELD_LINE, $lineNumber, self::GREATER_THAN);
    }

    /**
     * @param int $lineNumber
     * @return $this
     */
    public function lineLt($lineNumber) {
      return $this->addCondition(self::FIELD_LINE, $lineNumber, self::LESS_THAN);
    }


    /**
     * @param string $field
     * @param string|int|array $value
     * @param int $type
     * @throws Exception
     * @return $this
     */
    protected function addCondition($field, $value, $type) {
      $value = (array) $value;

      if (!isset($this->{$field}[$type])) {
        $this->{$field}[$type] = array();
      }

      $this->{$field}[$type] = array_merge($this->{$field}[$type], $value);
      return $this;
    }

    /**
     * @inheritdoc
     */
    public function isValid(\Funivan\PhpTokenizer\Token $token) {

      # check type
      if (!empty($this->type) and !$this->validateType($token)) {
        return false;
      }

      if (!empty($this->line) and !$this->validateLine($token)) {
        return false;
      }

      if (!empty($this->value) and !$this->validateValue($token)) {
        return false;
      }

      return true;
    }

    /**
     * @param string $field
     * @param string $value
     * @return bool
     */
    protected function validate($field, $value) {

      if (!$this->validateIs($field, $value)) {
        return false;
      }
      if (!$this->validateNot($field, $value)) {
        return false;
      }

      return true;
    }

    /**
     * @param $field
     * @param $value
     * @return bool
     */
    protected function validateIs($field, $value) {
      if (!isset($this->{$field}[self::IS])) {
        # we do not have any conditions
        return true;
      }
      return in_array($value, $this->{$field}[self::IS]);
    }

    /**
     * @param $field
     * @param $value
     * @return bool
     */
    protected function validateNot($field, $value) {

      if (!isset($this->{$field}[self::NOT])) {
        # we do not have any conditions
        return true;
      }
      return !in_array($value, $this->{$field}[self::NOT]);
    }

    /**
     * @param Token $token
     * @return bool
     */
    private function validateType($token) {

      if (!$this->validateIs(self::FIELD_TYPE, $token->getType())) {
        return false;
      }

      if (!$this->validateNot(self::FIELD_TYPE, $token->getType())) {
        return false;
      }

      return true;
    }

    /**
     * @param Token $token
     * @return bool
     */
    private function validateLine(Token $token) {
      $line = $token->getLine();

      $conditions = $this->line;


      # check line
      if (!$this->validateIsCondition($conditions, $line)) {
        return false;
      }

      if (!$this->validateNotCondition($conditions, $line)) {
        return false;
      }

      if (!empty($conditions[self::GREATER_THAN])) {
        foreach ($conditions[self::GREATER_THAN] as $lineGreaterThan) {
          if ($line <= $lineGreaterThan) {
            return false;
          }
        }
      }

      if (!empty($conditions[self::LESS_THAN])) {
        foreach ($conditions[self::LESS_THAN] as $lineLessThan) {
          if ($token->getLine() >= $lineLessThan) {
            return false;
          }
        }
      }

      return true;
    }

    /**
     * @param Token $token
     * @return bool
     */
    private function validateValue(Token $token) {

      $value = $token->getValue();

      $conditions = $this->value;

      # check line
      if (!$this->validateIsCondition($conditions, $value)) {
        return false;
      }

      if (!$this->validateNotCondition($conditions, $value)) {
        return false;
      }

      # check value regexp
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

    /**
     * @param string|int|array $value
     * @return array Array<String>
     * @throws Exception
     */
    protected function prepareValues($value) {
      $value = (array) $value;
      foreach ($value as $k => $val) {
        if (!is_string($val) and !is_numeric($val)) {
          throw new Exception('Invalid value. Must be string');
        }

        $value[$k] = (string) $val;
      }
      return $value;
    }

    /**
     * @param $conditions
     * @param $value
     * @return bool
     */
    private function validateIsCondition($conditions, $value) {
      return (!isset($conditions[static::IS]) or in_array($value, $conditions[static::IS]));
    }

    /**
     * @param $conditions
     * @param $line
     * @return bool
     */
    private function validateNotCondition($conditions, $line) {
      return (!isset($conditions[static::NOT]) or !in_array($line, $conditions[static::NOT]));
    }

  }