<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer;


  /**
   * @author Ivan Shcherbak <dev@funivan.com> 11/25/13
   */
  class QueryTest extends \Test\Funivan\PhpTokenizer\Main {

    public function _testSimpleFind() {
      $file = new \Funivan\PhpTokenizer\File($this->getDemoDataDir() . '/demo.php');
      $q = $file->getCollection()->query();

      $q->valueIs(1);

      $this->assertEquals(1, $q->getTokensNum());
    }

    public function _testLine() {

      $collection = $this->getTestCollection();

      $q = $collection->query()->lineIs(1);
      $this->assertEquals($collection->count(), $q->getTokensNum());

      $q = $collection->query()->lineIs([10]);
      $this->assertEquals(0, $q->getTokensNum());

      $q = $collection->query()->lineNot(1);
      $this->assertEquals(0, $q->getTokensNum());

      $q = $collection->query()->lineGt(0);
      $this->assertEquals($collection->count(), $q->getTokensNum());

      $q = $collection->query()->lineGt(1);
      $this->assertEquals(0, $q->getTokensNum());

      $q = $collection->query()->lineLt(2);
      $this->assertEquals($collection->count(), $q->getTokensNum());

      $q = $collection->query()->lineLt(1);

      $this->assertEquals(0, $q->getTokensNum());

    }

    /**
     * @return \Funivan\PhpTokenizer\Collection
     */
    protected function getTestCollection() {
      $collection = \Funivan\PhpTokenizer\Collection::parseFromString('<?php echo 123;');
      return $collection;
    }


    public function _testType() {
      $collection = $this->getTestCollection();
      $q = $collection->query()->typeNot(T_ECHO);
      $this->assertEquals($collection->count() - 1, $q->getTokensNum());
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
