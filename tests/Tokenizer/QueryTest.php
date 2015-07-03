<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Exception\InvalidArgumentException;
  use Funivan\PhpTokenizer\Query\Query;
  use Funivan\PhpTokenizer\Token;
  use Funivan\PhpTokenizer\TokenFinder;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 11/25/13
   */
  class QueryTest extends \Test\Funivan\PhpTokenizer\MainTestCase {



    public function testType() {

      $collection = Collection::initFromString('<?php echo $user;');
      $finder = new TokenFinder($collection);

      $query = new Query();
      $query->typeIs(T_ECHO);
      $this->assertCount(1, $finder->find($query));

      $query = new Query();
      $query->typeIs([T_ECHO, T_VARIABLE]);
      $this->assertCount(2, $finder->find($query));

      $query = new Query();
      $query->typeNot(T_ECHO);
      $this->assertCount(count($collection) - 1, $finder->find($query));

    }

    /**
     * @return array
     */
    public function testValue() {

      $collection = Collection::initFromString('<?php 
        echo 1; 
      
      ');

      $query = new Query();
      $query->valueNot('echo');
      $this->assertCount($collection->count() - 1, $collection->find($query));


      $collection = Collection::initFromString('<?php echo "123"; echo "132";');
      $this->assertCount(4, $collection->find(Query::create()->valueIs(['echo', ';'])));


      $q = Query::create();
      $this->assertCount(2, $collection->find($q->valueLike('/\d+/')));

      $this->assertCount(1, $collection->find($q->valueLike('/12\d+/')));

      $this->assertCount(0, $collection->find($q->valueIs(null)));

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

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidLineGreaterThen() {
      $query = new Query();
      $query->lineGt(1.343);
    }


    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidLineLessThen() {
      $query = new Query();
      $query->lineLt(array(new \stdClass()));
    }


    public function testQueryWithoutConditions() {
      $query = new Query();
      $token = new Token();
      $this->assertTrue($query->isValid($token));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidLineCheck() {
      $query = new Query();
      $query->lineIs(new \stdClass());

    }

    public function testIndexIs() {
      $query = new Query();
      $query->indexIs(1);
      $token = new Token();
      $token->setIndex(1);

      $this->assertTrue($query->isValid($token));
      $token->setIndex(3);
      $this->assertFalse($query->isValid($token));
    }

    public function testIndexIsMultipleDefinition() {
      $query = new Query();
      $query->indexIs([4, 5, 6, 1]);
      $token = new Token();
      $token->setIndex(1);

      $this->assertTrue($query->isValid($token));
      $token->setIndex(10);
      $this->assertFalse($query->isValid($token));
    }

    public function testIndexNot() {
      $query = new Query();
      $query->indexIs(1);
      $token = new Token();
      $token->setIndex(10);

      $this->assertFalse($query->isValid($token));
      $token->setIndex(1);
      $this->assertTrue($query->isValid($token));
    }


    public function testIndexNotMultipleDefinition() {
      $query = new Query();
      $query->indexNot([4, 5, 6, 1]);
      $token = new Token();
      $token->setIndex(2);

      $this->assertTrue($query->isValid($token));

      $token->setIndex(4);
      $this->assertFalse($query->isValid($token));
    }

    public function testLt() {
      $query = new Query();
      $query->indexLt(10);
      $token = new Token();
      $token->setIndex(10);

      $this->assertFalse($query->isValid($token));
      $token->setIndex(8);
      $this->assertTrue($query->isValid($token));

      $query = new Query();
      $query->indexLt([40, 30, 20]);
      $this->assertTrue($query->isValid($token));

      $token->setIndex(35);
      $this->assertFalse($query->isValid($token));
      
    }
    
    public function testGt() {
      $query = new Query();
      $query->indexGt(10);
      
      $token = new Token();
      $token->setIndex(10);

      $this->assertFalse($query->isValid($token));
      
      
      $token->setIndex(11);
      $this->assertTrue($query->isValid($token));

      $query = new Query();
      $query->indexGt([40, 30, 20]);
      $this->assertFalse($query->isValid($token));

      $token->setIndex(21);
      $this->assertFalse($query->isValid($token));
      
      $token->setIndex(40);
      $this->assertFalse($query->isValid($token));
      
      $token->setIndex(41);
      $this->assertTrue($query->isValid($token));
      
    }

  }
