<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Exception\Exception;
  use Funivan\PhpTokenizer\Exception\InvalidArgumentException;
  use Funivan\PhpTokenizer\Query\Query;
  use Funivan\PhpTokenizer\Token;
  use Funivan\PhpTokenizer\TokenFinder;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 11/25/13
   */
  class QueryTest extends \Test\Funivan\PhpTokenizer\MainTestCase {


    public function testType() {

      $collection = Collection::createFromString('<?php echo $user;');
      $finder = new TokenFinder($collection);

      $query = new Query();
      $query->typeIs(T_ECHO);
      static::assertCount(1, $finder->find($query));

      $query = new Query();
      $query->typeIs([T_ECHO, T_VARIABLE]);
      static::assertCount(2, $finder->find($query));

      $query = new Query();
      $query->typeNot(T_ECHO);
      static::assertCount(count($collection) - 1, $finder->find($query));

    }

    /**
     * @return array
     */
    public function testValue() {

      $collection = Collection::createFromString('<?php 
        echo 1; 
      
      ');

      $query = new Query();
      $query->valueNot('echo');
      static::assertCount($collection->count() - 1, $collection->find($query));


      $collection = Collection::createFromString('<?php echo "123"; echo "132";');
      static::assertCount(4, $collection->find(Query::create()->valueIs(['echo', ';'])));


      $q = Query::create();
      static::assertCount(2, $collection->find($q->valueLike('/\d+/')));

      static::assertCount(0, $collection->find($q->valueLike(null)));

      static::assertCount(0, $collection->find($q->valueIs(null)));

    }


    /**
     * @expectedException InvalidArgumentException
     */
    public function testWithInvalidValue() {
      $query = new Query();
      $query->valueNot(new \stdClass());
    }


    /**
     * @expectedException InvalidArgumentException
     */
    public function testWithInvalidArrayValue() {
      $query = new Query();
      $query->valueNot(array(new \stdClass()));

    }


    public function testQueryWithoutConditions() {
      $query = new Query();
      $token = new Token();
      static::assertTrue($query->isValid($token));
    }

    public function testIndexIs() {
      $query = new Query();
      $query->indexIs(1);
      $token = new Token();
      $token->setIndex(1);

      static::assertTrue($query->isValid($token));
      $token->setIndex(3);
      static::assertFalse($query->isValid($token));
    }

    public function testIndexIsMultipleDefinition() {
      $query = new Query();
      $query->indexIs([4, 5, 6, 1]);
      $token = new Token();
      $token->setIndex(1);

      static::assertTrue($query->isValid($token));
      $token->setIndex(10);
      static::assertFalse($query->isValid($token));
    }

    public function testIndexNot() {
      $query = new Query();
      $query->indexIs(1);
      $token = new Token();
      $token->setIndex(10);

      static::assertFalse($query->isValid($token));
      $token->setIndex(1);
      static::assertTrue($query->isValid($token));
    }


    public function testIndexNotMultipleDefinition() {
      $query = new Query();
      $query->indexNot([4, 5, 6, 1]);
      $token = new Token();
      $token->setIndex(2);

      static::assertTrue($query->isValid($token));

      $token->setIndex(4);
      static::assertFalse($query->isValid($token));
    }

    public function testLt() {
      $query = new Query();
      $query->indexLt(10);
      $token = new Token();
      $token->setIndex(10);

      static::assertFalse($query->isValid($token));
      $token->setIndex(8);
      static::assertTrue($query->isValid($token));

      $query = new Query();
      $query->indexLt([40, 30, 20]);
      static::assertTrue($query->isValid($token));

      $token->setIndex(35);
      static::assertFalse($query->isValid($token));

    }

    public function testGt() {
      $query = new Query();
      $query->indexGt(10);

      $token = new Token();
      $token->setIndex(10);

      static::assertFalse($query->isValid($token));


      $token->setIndex(11);
      static::assertTrue($query->isValid($token));

      $query = new Query();
      $query->indexGt([40, 30, 20]);
      static::assertFalse($query->isValid($token));

      $token->setIndex(21);
      static::assertFalse($query->isValid($token));

      $token->setIndex(40);
      static::assertFalse($query->isValid($token));

      $token->setIndex(41);
      static::assertTrue($query->isValid($token));

    }


    public function testPrepareNullValueCondition() {

      $q = new Query();
      $q->valueIs(null);

      $token = new Token();

      static::assertFalse($q->isValid($token));
    }

    /**
     * @throws \Funivan\PhpTokenizer\Exception\Exception
     */
    public function testPrepareNullIntValues() {

      $q = new Query();
      $q->typeIs(null);

      $token = new Token();

      static::assertFalse($q->isValid($token));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testPrepareObjectIntValues() {

      $q = new Query();
      /** @noinspection PhpParamsInspection */
      $q->typeIs(new \stdClass());

    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testPrepareArrayOfInvalidValuesForIntValueCondition() {

      $q = new Query();
      $q->typeIs([null]);

    }

    public function testCustomCallback() {
      $q = new Query();
      $q->custom(function (Token $token) {

        return ($token->isValid() and $token->getLine() < 10);
      });

      $token = new Token();
      static::assertFalse($q->isValid($token));

      $token = new Token();
      $token->setValue("test");
      $token->setLine(5);
      static::assertTrue($q->isValid($token));

    }

    /**
     * @expectedException Exception
     */
    public function testCustomInvalidCallback() {
      $q = new Query();
      $q->custom(function (Token $token) {
        return $token;
      });

      static::assertFalse($q->isValid(new Token()));

    }

  }
