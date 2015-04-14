<?php


  namespace Funivan\PhpTokenizer\Query;

  /**
   * @author Ivan Shcherbak <dev@funivan.com>
   */
  class Query implements QueryInterface {

    const N = __CLASS__;

    const IS = 1;

    const NOT = 2;

    const GREATER_THAN = 3;

    const LESS_THAN = 5;

    const LESS_THAN_EQUAL = 6;

    const REXEG = 7;

    const FIELD_VALUE = 'value';

    const FIELD_TYPE = 'type';

    const FIELD_LINE = 'line';

    protected $type = array();

    protected $value = array();

    protected $line = array();

    /**
     * @return static
     */
    public static function create() {
      return new static();
    }
    

    /**
     * @param int $type
     * @return $this
     */
    public function typeIs($type) {
      return $this->addCondition(self::FIELD_TYPE, $type, self::IS);
    }

    /**
     * @param int $type
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
      return $this->addCondition(self::FIELD_VALUE, $value, self::IS);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function valueNot($value) {
      return $this->addCondition(self::FIELD_VALUE, $value, self::NOT);
    }

    /**
     * @param string $regexp
     * @return $this
     */
    public function valueLike($regexp) {
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
     * @param string|int $value
     * @param int $type
     * @throws \Funivan\PhpTokenizer\Exception
     * @return $this
     */
    protected function addCondition($field, $value, $type) {
      $value = (array) $value;

      if (!isset($this->{$field}[$type])) {
        $this->{$field}[$type] = array();
      }

      # Check value type. Must be string
      if ($field == self::FIELD_VALUE) {
        foreach ($value as $k => $val) {
          if (!is_string($val) and !is_numeric($val)) {
            throw new \Funivan\PhpTokenizer\Exception('Invalid value. Must be string');
          }

          $value[$k] = (string) $val;
        }
      }

      $this->{$field}[$type] = array_merge($this->{$field}[$type], $value);
      return $this;
    }

    /**
     * @inheritdoc
     */
    public function isValid(\Funivan\PhpTokenizer\Token $token) {
      # check type
      if (!$this->validate(self::FIELD_TYPE, $token->getType())) {
        return false;
      }

      # check value
      if (!$this->validate(self::FIELD_VALUE, $token->getValue())) {
        return false;
      }

      # check line
      if (!$this->validate(self::FIELD_LINE, $token->getLine())) {
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

      if (!empty($this->{$field}[self::IS]) and !in_array($value, $this->{$field}[self::IS])) {
        return false;
      }

      if (!empty($this->{$field}[self::NOT]) and in_array($value, $this->{$field}[self::NOT])) {
        return false;
      }

      if ($field == self::FIELD_VALUE and !empty($this->{$field}[self::REXEG])) {
        foreach ($this->{$field}[self::REXEG] as $regex) {
          if (!preg_match($regex, $value)) {
            return false;
          }
        }
      }

      if (in_array($field, [self::FIELD_LINE])) {
        if (!empty($this->{$field}[self::GREATER_THAN])) {
          foreach ($this->{$field}[self::GREATER_THAN] as $lineGreaterThan) {
            if ($value <= $lineGreaterThan) {
              return false;
            }
          }
        }


        if (!empty($this->{$field}[self::LESS_THAN])) {
          foreach ($this->{$field}[self::LESS_THAN] as $lineLessThan) {
            if ($value >= $lineLessThan) {
              return false;
            }
          }
        }

      }

      return true;
    }

  }