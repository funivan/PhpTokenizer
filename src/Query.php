<?php


  namespace Funivan\PhpTokenizer;

  use Funivan\PhpTokenizer\Query\Base;

  /**
   * @author Ivan Shcherbak <dev@funivan.com>
   */
  class Query extends Base {

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
     * @param $type
     * @return $this
     */
    public function typeIs($type) {
      return $this->addCondition(self::FIELD_TYPE, $type, self::IS);
    }

    /**
     * @param $type
     * @return $this
     */
    public function typeNot($type) {
      return $this->addCondition(self::FIELD_TYPE, $type, self::NOT);
    }

    /**
     * @param $value
     * @return $this
     */
    public function valueIs($value) {
      return $this->addCondition(self::FIELD_VALUE, $value, self::IS);
    }

    /**
     * @param $value
     * @return $this
     */
    public function valueNot($value) {
      return $this->addCondition(self::FIELD_VALUE, $value, self::NOT);
    }

    /**
     * @param $regexp
     * @return $this
     */
    public function valueLike($regexp) {
      return $this->addCondition(self::FIELD_VALUE, $regexp, self::REXEG);
    }


    /**
     * @param $lineNumber
     * @return $this
     */
    public function lineIs($lineNumber) {
      return $this->addCondition(self::FIELD_LINE, $lineNumber, self::IS);
    }

    /**
     * @param $lineNumber
     * @return $this
     */
    public function lineNot($lineNumber) {
      return $this->addCondition(self::FIELD_LINE, $lineNumber, self::NOT);
    }

    /**
     * @param $lineNumber
     * @return $this
     */
    public function lineGt($lineNumber) {
      return $this->addCondition(self::FIELD_LINE, $lineNumber, self::GREATER_THAN);
    }

    /**
     * @param $lineNumber
     * @return $this
     */
    public function lineLt($lineNumber) {
      return $this->addCondition(self::FIELD_LINE, $lineNumber, self::LESS_THAN);
    }


    /**
     * @param $field
     * @param $value
     * @param $type
     * @throws \Funivan\PhpTokenizer\Exception
     * @return $this
     */
    protected function addCondition($field, $value, $type) {
      $this->cleanCache();

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
     * @return Collection
     */
    public function getTokens() {
      if ($this->cache === null) {
        $this->parse();
      }

      return $this->cache;
    }

    /**
     * Return number of find tokens
     * @return int
     */
    public function getTokensNum() {
      return count($this->getTokens());
    }

    /**
     * @return $this
     */
    protected function parse() {
      $this->cleanCache();

      $tokensResult = [];
      foreach ($this->collection as $token) {
        if ($this->isValid($token)) {
          $tokensResult[] = $token;
        }
      }

      $collection = new Collection();
      $collection->setItems($tokensResult);
      $this->cache = $collection;

      return $this;
    }

    /**
     * @param Token $token
     * @return bool
     */
    public function isValid(Token $token) {
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