<?php

  require 'vendor/autoload.php';

  class Q {

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
     * @return \Funivan\PhpTokenizer\Token
     */
    public function token() {
      return !empty($this->collection[$this->position]) ? $this->collection[$this->position] : null;
    }

    public function valueIs($value) {
      if (!$this->skipCondition and $this->token()->getValue() == $value) {
        $token = $this->token();
        $this->position++;
        return $token;
      }
      $this->skipCondition = true;
      return new \Funivan\PhpTokenizer\Token([1, '', -1]);
    }

    public function typeIs($type) {
      if (!$this->skipCondition and $this->token()->getType() == $type) {
        $token = $this->token();
        $this->position++;
        return $token;
      }
      $this->skipCondition = true;
      return new \Funivan\PhpTokenizer\Token([1, '', -1]);
    }

    public function typePossible($type) {
      if (!$this->skipCondition and $this->token()->getType() == $type) {
        $token = $this->token();
        $this->position++;
        return $token;
      }
      return new \Funivan\PhpTokenizer\Token([1, '', -1]);
    }

    public function any() {
      $token = $this->token();
      $this->position++;
      return $token;
    }

    public function possible() {
      $token = $this->token();
      if ($token !== null) {
        $this->position++;
      }
      return $token;
    }

    public function valid() {
      return $this->skipCondition === false;
    }

  }

  class Finder extends \Funivan\PhpTokenizer\Collection {

    public function getNext($step = 1) {
      return parent::getNext($step);
    }

    /**
     * @return Q
     */
    public function go() {

      if ($this->valid() == false) {
        return null;
      }

      $q = new Q($this->position, $this);

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

  /** @var Q $q */
  while ($q = $finder->go()) {
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
