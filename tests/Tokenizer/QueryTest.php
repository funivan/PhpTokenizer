<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Query\Query;
  use Funivan\PhpTokenizer\TokenFinder;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 11/25/13
   */
  class QueryTest extends \Test\Funivan\PhpTokenizer\Main {


    public function testLine() {

      $collection = Collection::initFromString('<?php echo 1;');
      $finder = new TokenFinder($collection);

      $query = new Query();
      $query->lineIs(1);
      $this->assertCount($collection->count(), $finder->find($query));

      $query = new Query();
      $query->lineIs(10);

      $this->assertCount(0, $finder->find($query));

      $query = new Query();
      $query->lineNot(1);
      $this->assertCount(0, $finder->find($query));

      $query = new Query();
      $query->lineGt(1);
      $this->assertCount(0, $finder->find($query));

      $query = new Query();
      $query->lineLt(1);
      $this->assertCount(0, $finder->find($query));

    }


    public function testType() {

      $collection = Collection::initFromString('<?php echo $user;');
      $finder = new TokenFinder($collection);

      $query = new Query();
      $query->typeIs(T_ECHO);
      $this->assertCount(1, $finder->find($query));
      
      $query = new Query();
      $query->typeIs([T_ECHO,T_VARIABLE]);
      $this->assertCount(2, $finder->find($query));

      $query = new Query();
      $query->typeNot(T_ECHO);
      $this->assertCount(count($collection) - 1, $finder->find($query));

      
    }

    /**
     * @return array
     */
    public function _testValue() {

      $collection = $this->getTestCollection();
      $q = $collection->query()->valueNot('echo');
      $this->assertEquals($collection->count() - 1, $q->getTokensNum());

      $q = $collection->query()->valueLike('!e[ch]{2}o!');
      $this->assertEquals(1, $q->getTokensNum());

      $error = null;
      try {
        $collection->query()->valueLike(array(new \stdClass()));
      } catch (\Exception $error) {

      }
      $this->assertInstanceOf('Exception', $error);

    }

  }
