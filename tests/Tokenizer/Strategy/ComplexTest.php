<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer\Strategy;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\TokenStream;
  use Funivan\PhpTokenizer\Strategy\Possible;
  use Funivan\PhpTokenizer\Token;

  class ComplexTest extends \PHPUnit_Framework_TestCase {

    public function testSkipWhitespaces() {
      $code = '<?php
      echo $a;
      echo $a  ;
      echo $a
      
      ;
      ';
      $finder = new TokenStream(Collection::initFromString($code), true);

      $findItems = array();
      while ($q = $finder->iterate()) {

        $list = $q->sequence(['echo', '$a', ';']);
        if ($q->isValid()) {
          $findItems[] = $list;
        }
      }

      $this->assertCount(3, $findItems);
    }

    public function testWithoutWhitespaceSkip() {
      $code = '<?php 
      echo $a;
      echo $a  ;
      echo $a
      
      ;
      ';

      $sequenceConfiguration = array(
        array(
          'sequence' => array('echo', '$a', ';'),
          'items' => 0
        ),

        array(
          'sequence' => array('$a', ';'),
          'items' => 1
        ),

        array(
          'sequence' => array('echo', ' ', '$a', '  ', ';'),
          'items' => 1
        ),

        array(
          'sequence' => array('echo', T_WHITESPACE, '$a', T_WHITESPACE, ';'),
          'items' => 2
        ),

      );
      $collection = Collection::initFromString($code);

      foreach ($sequenceConfiguration as $itemInfo) {
        $finder = new TokenStream($collection);
        $findItems = array();
        while ($q = $finder->iterate(false)) {

          $list = $q->sequence($itemInfo['sequence']);
          if ($q->isValid()) {
            $findItems[] = $list;
          }
        }

        $this->assertEquals($itemInfo['items'], count($findItems));
      }

    }

    public function getComplexTestData() {
      return [
        [
          'if (!is_array($a1)){
            $a1 = (array) $a1;
          }'
          ,
          'containt' => '$a1 = (array) $a1;',
          'notContain' => 'is_array',
        ],
        [
          'if ( is_array( $a2 )==false){
              $a2 = (array) 
              $a2;
          }
          ',
          'containt' => '$a2 = (array) $a2;',
          'notContain' => 'is_array',
        ],
        [
          ' if (!is_array($a4) == false){
          $a4 = (array) $a4;
          }
        ',
          'containt' => 'is_array',
          'notContain' => null,
        ],
        [
          'if (is_array($a5)!=true){
          $a5 = (array) $a5;
          }',
          'containt' => '$a5 = (array) $a5;',
          'notContain' => 'is_array',
        ],
        [
          ' if (is_array($a6) !==
      true){
          $a6 = (array) $a6;
          }',
          'containt' => '$a6 = (array) $a6;',
          'notContain' => 'is_array',
        ],
        ['    if (is_array($a7)==true){
          $a7 = (array) $a7;
        }',
          'containt' => 'is_array',
          'notContain' => null,
        ],
        [
          ' if (is_array($a8) === false){
            $a8 = (array) $a8;
          }
        ',
          'containt' => '$a8 = (array) $a8;',
          'notContain' => 'is_array',
        ]
      ];
    }

    /**
     * @dataProvider   getComplexTestData
     * @throws \Funivan\PhpTokenizer\Exception\Exception
     */
    public function testComplex($code, $contain, $notContain) {
      $code = '<?php ' . $code;

      $collection = Collection::initFromString($code);

      $finder = new TokenStream($collection, true);

      while ($q = $finder->iterate()) {

        $start = $q->sequence(['if', '(', Possible::create()->valueIs('!'), 'is_array', '(']);

        $token = $q->strict(T_VARIABLE);
        $q->strict(')');

        if ($q->isValid() and $start[2]->isValid() == false) {
          if ($q->process(Possible::create()->valueIs(['==', '===']))->isValid()) {
            $q->strict('false');
          } elseif ($q->process(Possible::create()->valueIs(['!=', '!==']))->isValid()) {
            $q->strict('true');
          }
        }

        $last = $q->sequence(array(')', '{', $token->getValue(), '=', '(array)', $token->getValue(), ';', '}'));

        if ($q->isValid()) {
          $start = $start->getFirst()->getIndex();
          $end = $last->getLast()->getIndex();

          $newToken = new Token();
          $newToken->setValue($token->getValue() . ' = (array) ' . $token->getValue() . ';');

          foreach ($collection as $index => $collectionToken) {
            if ($index >= $start and $index <= $end) {
              $collectionToken->remove();
            }
          }

          $collection[$end] = $newToken;
        }

      }

      $result = (string)$collection;
      if ($contain == null and $notContain === null) {
        throw new \Exception("Please provide notContain or contain condition");
      }

      if ($contain !== null) {
        $this->assertContains($contain, $result);
      }

      if ($notContain !== null) {
        $this->assertNotContains($notContain, $result);
      }

    }

  }
