<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\QuerySequence\QuerySequence;
  use Test\Funivan\PhpTokenizer\MainTestCase;

  class StreamProcessTest extends MainTestCase {


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

  }