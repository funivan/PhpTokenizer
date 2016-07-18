<?php

  namespace Test\Funivan\PhpTokenizer\QuerySequence;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\QuerySequence\QuerySequence;
  use Funivan\PhpTokenizer\Strategy\Possible;
  use Funivan\PhpTokenizer\Strategy\Search;
  use Funivan\PhpTokenizer\Strategy\Strict;
  use Funivan\PhpTokenizer\Token;
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
      $collection = Collection::createFromString($code);

      $findItems = array();
      foreach ($collection as $index => $token) {
        $querySequence = new QuerySequence($collection, $index);
        $token = $querySequence->strict('echo');
        if ($querySequence->isValid()) {
          $findItems[] = $token;
        }
      }

      static::assertCount(3, $findItems);
    }

    /**
     *
     */
    public function testMoveToToken() {
      $code = '<?php echo $a;';
      $collection = Collection::createFromString($code);
      $lastToken = $collection->getLast();

      $finder = new QuerySequence($collection);
      $token = $finder->moveToToken($lastToken);
      static::assertEquals($lastToken, $token);


      $finder = new QuerySequence($collection);
      $token = $finder->moveToToken(new Token());
      static::assertNull($token->getValue());
      static::assertFalse($finder->isValid());
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
      $collection = Collection::createFromString($code);

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
      $collection = Collection::createFromString($code);


      $q = new QuerySequence($collection, 3);
      $q->strict($condition);
      static::assertEquals($isValid, $q->isValid());
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
      $collection = Collection::createFromString($code);


      $q = new QuerySequence($collection, 3);
      $token = $q->possible($condition);

      static::assertEquals($isValidToken, $token->isValid());
      static::assertTrue($q->isValid());

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
      $collection = Collection::createFromString($code);

      $q = new QuerySequence($collection, 3);
      $q->possible($condition);
    }


    /**
     *
     */
    public function testSectionWithoutEndDelimiter() {
      $code = '<?php foreach($users as $user ){ $a;';
      $collection = Collection::createFromString($code);

      $q = new QuerySequence($collection, 0);
      $section = $q->section('{', '}');

      static::assertCount(0, $section);
    }


    /**
     *  Check move strategy
     */
    public function testMove() {
      $code = '<?php echo 1';
      $collection = Collection::createFromString($code);

      $q = new QuerySequence($collection, 0);

      $token = $q->move(1);
      static::assertEquals('echo', $token->getValue());
      static::assertTrue($q->isValid());

      $token = $q->move(2);
      static::assertEquals('1', $token->getValue());
      static::assertTrue($q->isValid());

      $token = $q->move(-2);
      static::assertEquals('echo', $token->getValue());
      static::assertTrue($q->isValid());

      $token = $q->move(-100);
      static::assertNull($token->getValue());
      static::assertFalse($q->isValid());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetValidWithInvalidFlag() {
      $q = new QuerySequence(new Collection(), 0);
      /** @noinspection PhpParamsInspection */
      $q->setValid(new \stdClass());
    }


  }