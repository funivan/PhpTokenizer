<?php

  namespace Test\Funivan\PhpTokenizer\QuerySequence;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\QuerySequence\QuerySequence;
  use Funivan\PhpTokenizer\Strategy\Possible;
  use Funivan\PhpTokenizer\Strategy\Search;
  use Funivan\PhpTokenizer\Strategy\Strict;
  use Test\Funivan\PhpTokenizer\MainTestCase;

  /**
   *
   * @package Test\Funivan\PhpTokenizer\Tokenizer
   */
  class QuerySequenceTest extends MainTestCase {


    /**
     *
     */
    public function testSimpleIterate() {
      $code = '<?php 
      echo $a;
      echo $a;
      echo $a;
      ';
      $collection = Collection::initFromString($code);

      $findItems = array();
      foreach ($collection as $index => $token) {
        $querySequence = new QuerySequence($collection, $index);
        $token = $querySequence->strict('echo');
        if ($querySequence->isValid()) {
          $findItems[] = $token;
        }
      }

      $this->assertCount(3, $findItems);
    }

    /**
     *
     */
    public function testMoveTo() {
      $code = '<?php echo $a;';
      $collection = Collection::initFromString($code);
      $lastToken = $collection->getLast();

      $finder = new QuerySequence($collection);
      $token = $finder->moveTo($lastToken->getIndex());
      $this->assertEquals($lastToken, $token);
    }


    /**
     * @return array
     */
    public function getTestStrictInvalidConditionDataProvider() {
      return array(
        array(
          new \stdClass()
        ),

        array(
          new Possible()
        ),
        array(
          array()
        ),

      );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider getTestStrictInvalidConditionDataProvider
     * @param $condition
     */
    public function testStrictInvalidCondition($condition) {

      $code = '<?php echo $a;';
      $collection = Collection::initFromString($code);

      $q = new QuerySequence($collection, 3);
      $q->strict($condition);
    }

    /**
     * @return array
     */
    public function getTestStrictConditionDataProvider() {
      return array(
        array(
          T_VARIABLE,
          true,
        ),

        array(
          T_WHITESPACE,
          false,
        ),

        array(
          null,
          false,
        ),

        array(
          '$a',
          true,
        ),

        array(
          '$b',
          false,
        ),

        array(
          Strict::create()->valueLike('!^\$.*!'),
          true,
        ),

        array(
          Strict::create()->valueLike('!^\$.*!')->typeIs(T_WHITESPACE),
          false,
        ),

      );
    }


    /**
     * @dataProvider getTestStrictConditionDataProvider
     * @param $condition
     * @param $isValid
     */
    public function testStrictCondition($condition, $isValid) {

      $code = '<?php echo $a;';
      $collection = Collection::initFromString($code);


      $q = new QuerySequence($collection, 3);
      $q->strict($condition);
      $this->assertEquals($isValid, $q->isValid());
    }


    /**
     * @return array
     */
    public function getTestPossibleConditionDataProvider() {
      return array(
        array(
          T_VARIABLE,
          true,
        ),
        array(
          T_WHITESPACE,
          false,
        ),
        array(
          null,
          false,
        ),

        array(
          '$a',
          true,
        ),

        array(
          '$b',
          false,
        ),

        array(
          Possible::create()->valueLike('!^\$.*!'),
          true,
        ),

        array(
          Possible::create()->valueLike('!^\$.*!')->typeIs(T_WHITESPACE),
          false,
        ),

      );
    }

    /**
     * @dataProvider getTestPossibleConditionDataProvider
     * @param $condition
     * @param $isValidToken
     */
    public function testPossibleCondition($condition, $isValidToken) {

      $code = '<?php echo $a;';
      $collection = Collection::initFromString($code);


      $q = new QuerySequence($collection, 3);
      $token = $q->possible($condition);

      $this->assertEquals($isValidToken, $token->isValid());
      $this->assertTrue($q->isValid());

    }


    /**
     * @return array
     */
    public function getTestPossibleInvalidConditionDataProvider() {
      return array(
        array(
          new \stdClass()
        ),

        array(
          new Strict()
        ),
        array(
          array()
        ),
        array(
          Search::create()
        ),

      );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider getTestPossibleInvalidConditionDataProvider
     * @param $condition
     */
    public function testPossibleInvalidCondition($condition) {

      $code = '<?php echo $a;';
      $collection = Collection::initFromString($code);

      $q = new QuerySequence($collection, 3);
      $q->possible($condition);
    }


    /**
     *
     */
    public function testSectionWithoutEndDelimiter() {
      $code = '<?php foreach($users as $user ){ $a;';
      $collection = Collection::initFromString($code);

      $q = new QuerySequence($collection, 0);
      $section = $q->section('{', '}');

      $this->assertCount(0, $section);
    }


    /**
     *  Check move strategy
     */
    public function testMove() {
      $code = '<?php echo 1';
      $collection = Collection::initFromString($code);

      $q = new QuerySequence($collection, 0);

      $token = $q->move(1);
      $this->assertEquals('echo', $token->getValue());
      $this->assertTrue($q->isValid());

      $token = $q->move(2);
      $this->assertEquals('1', $token->getValue());
      $this->assertTrue($q->isValid());

      $token = $q->move(-2);
      $this->assertEquals('echo', $token->getValue());
      $this->assertTrue($q->isValid());

      $token = $q->move(-100);
      $this->assertNull($token->getValue());
      $this->assertFalse($q->isValid());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetValidWithInvalidFlag() {
      $q = new QuerySequence(new Collection(), 0);
      $q->setValid(new \stdClass());
    }
    
    
  }