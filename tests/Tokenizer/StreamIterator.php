<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\StreamProcess\StreamProcess;
  use Test\Funivan\PhpTokenizer\MainTestCase;

  class StreamIterator extends MainTestCase {


    public function testSimpleIterate() {
      $code = '<? 
      echo $a;
      echo $a;
      echo $a;
      ';
      $finder = new StreamProcess(Collection::initFromString($code));

      $findItems = array();
      foreach ($finder as $processor) {
        $token = $processor->strict('echo');
        if ($processor->isValid()) {
          $findItems[] = $token;
        }
      }

      $this->assertCount(3, $findItems);
    }

    public function testMoveTo() {
      $code = '<? echo $a;';
      $collection = Collection::initFromString($code);

      $lastToken = $collection->getLast();


      $finder = new StreamProcess($collection);
      $token = $finder->moveTo($lastToken->getIndex());
      $this->assertEquals($lastToken, $token);
    }

  }