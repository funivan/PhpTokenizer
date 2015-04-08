<?php

  require 'vendor/autoload.php';

  class TokenDummy extends \Funivan\PhpTokenizer\Token {

    /**
     * @param array $data
     * @throws Exception
     */
    public function __construct(array $data = []) {
      if (!empty($data)) {
        throw new \Exception("You cant create dummy with any data");
      }

      $this->line = static::INVALID_LINE;
      $this->value = static::INVALID_VALUE;
      $this->type = static::INVALID_TYPE;
      $this->position = static::INVALID_POSITION;
    }

    /**
     *
     */
    public static function create() {
      return new static();
    }

    /**
     * @internal
     * @param array $data
     * @return $this|void
     * @throws Exception
     */
    protected function setData(array $data) {
      throw new \Exception('This token is dummy and cant be changed');
    }

    /**
     * @internal
     * @param $type
     * @return $this|void
     * @throws Exception
     */
    public function setType($type) {
      throw new \Exception('This token is dummy and cant be changed');
    }

    /**
     * @internal
     * @param int|string $value
     * @return $this|void
     * @throws Exception
     */
    public function setValue($value) {
      throw new \Exception('This token is dummy and cant be changed');
    }

    /**
     * @internal
     * @param int $line
     * @return $this|void
     * @throws Exception
     */
    public function setLine($line) {
      throw new \Exception('This token is dummy and cant be changed');
    }

    /**
     * @internal
     * @param null $position
     * @return $this|void
     * @throws Exception
     */
    public function setPosition($position) {
      throw new \Exception('This token is dummy and cant be changed');
    }

  }

  class TokenStreamProcess {

    private $position;

    /**
     * @var \Funivan\PhpTokenizer\Collection
     */
    private $collection;

    private $skipCondition = false;

    function __construct($position, \Funivan\PhpTokenizer\Collection $collection) {
      $this->position = $position;
      $this->collection = $collection;
    }

    /**
     * Return current token
     *
     * @return \Funivan\PhpTokenizer\Token
     */
    private function token() {
      return !empty($this->collection[$this->position]) ? $this->collection[$this->position] : null;
    }

    public function valueIs($value) {
      if (!$this->skipCondition and $this->token()->getValue() == $value) {
        $token = $this->token();
        $token->setPosition($this->position);
        $this->position++;
        return $token;
      }
      $this->skipCondition = true;
      return TokenDummy::create();
    }

    public function typeIs($type) {
      if (!$this->skipCondition and $this->token()->getType() == $type) {
        $token = $this->token();
        $token->setPosition($this->position);
        $this->position++;
        return $token;
      }
      $this->skipCondition = true;
      return TokenDummy::create();
    }

    public function typePossible($type) {
      if (!$this->skipCondition and $this->token()->getType() == $type) {
        $token = $this->token();
        $token->setPosition($this->position);
        $this->position++;
        return $token;
      }
      return TokenDummy::create();
    }

    public function any() {
      $token = $this->token();
      $token->setPosition($this->position);
      $this->position++;
      return $token;
    }

    public function possible() {
      $token = $this->token();
      $token->setPosition($this->position);
      if ($token !== null) {
        $this->position++;
      }
      return $token;
    }

    public function valid() {
      return $this->skipCondition === false;
    }

  }

  /**
   * @method static Finder initFromString($code)
   */
  class Finder extends \Funivan\PhpTokenizer\Collection {

    public function getNext($step = 1) {
      return parent::getNext($step);
    }

    /**
     * Return new Query
     * @return TokenStreamProcess
     */
    public function iterate() {

      if ($this->valid() == false) {
        return null;
      }

      $q = new TokenStreamProcess($this->position, $this);
      ++$this->position;

      return $q;
    }

  }

  $code = '<?
  
  $table->fetchAll(
    $table->select()
  );
  
  $db-> fetchAll($db->select());
  $dd-> fetchAll($rf->select());
  
  
  
  
  
  
  
  
  ';
  $finder = Finder::initFromString($code);

  /** @var TokenStreamProcess $q */
  while ($q = $finder->iterate()) {

    $token = $q->typeIs(T_VARIABLE);
    $q->valueIs('->');
    $q->typePossible(T_WHITESPACE);
    $q->valueIs('fetchAll');
    $q->valueIs('(');
    $q->typePossible(T_WHITESPACE);
    $last = $q->valueIs($token->getValue());
    $nex = $q->valueIs('->');
    $select = $q->valueIs('select');
    $b1 = $q->valueIs('(');
    $b2 = $q->valueIs(')');

    if (!$q->valid()) {
      continue;
    }

    $b1->remove();
    $b2->remove();
    $select->remove();
    $nex->remove();
    $last->remove();

    $token->appendToValue('->select()');
//    $last->setValue('$database');
//
//    if (!empty($possibleSpace)) {
//      $possibleSpace->setValue("");
//    }

  }

  echo $finder;



  //  while ($q = $finder->getNext()) {
  //
  //    $token = $q->typeIs(T_VARIABLE);
  //    $ref = $q->valueIs('->');
  //    $fetchAll = $q->valueIs('fetchAll');
  //    $start = $q->sectionStart('(');
  //    $q->valueIs($token->getValue());
  //    $q->valueIs('->');
  //    $q->valueIs('section');
  //    $q->any();
  //    $end = $q->sectionEnd(')');
  //
  //    $q->remove($token, $ref, $fetchAll, $start);
  //
  //    $end->appendValue('->fetchAll()');
  //
  //  }
