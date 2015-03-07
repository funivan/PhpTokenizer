<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer\Query;

  class ExtendedTest extends \Test\Funivan\PhpTokenizer\Main {

    /**
     * @return \Funivan\PhpTokenizer\File
     */
    protected function getTestFile() {
      $code = <<<CODE
<?php "a"."b";
"c" . "d";
"1". "2";
"3" ."4";

CODE;

      $file = $this->initFileWithCode($code);
      return $file;
    }

    public function _testInsertWhitespace() {

      $file = $this->getTestFile();

      $eq = $file->getCollection()->extendedQuery();
      $eq->strict()->typeIs(T_CONSTANT_ENCAPSED_STRING);
      $eq->strict()->valueIs('.');
      $eq->strict()->typeIs(T_CONSTANT_ENCAPSED_STRING);

      $this->assertCount(3, $eq->getQueries());

      $eq->insertWhitespaceQueries();

      $this->assertCount(5, $eq->getQueries());

      $block = $eq->getBlock();

      $this->assertCount(4, $block);

    }

    public function _testParse() {

      $file = $this->getTestFile();

      $error = null;
      try {
        $eq = $file->getCollection()->extendedQuery();
        $eq->getBlock();
      } catch (\Exception $error) {

      }

      $this->assertInstanceOf('Exception', $error);

//      die();
//
//      echo "\n***" . __LINE__ . "***\n<pre>" . print_r($eq->getBlock(), true) . "</pre>\n";
//      die();
//
//      $this->assertCount(0, $eq->getBlock());

    }

    public function _testIndexes() {

      $file = $this->getTestFile();
      $eq = $file->getCollection()->extendedQuery();
      $eq->strict()->valueIs('"4"');
      $eq->strict()->valueIs(';');

      $this->assertCount(1, $eq->getEndIndexes());
      $this->assertCount(1, $eq->getStartIndexes());

      # check cache
      $this->assertCount(1, $eq->getEndIndexes());
      $this->assertCount(1, $eq->getStartIndexes());
      $this->assertCount(1, $eq->getBlock());

      $eq = $file->getCollection()->extendedQuery();
      $eq->strict()->valueIs('"5"');
      $eq->strict()->valueIs(';');

      $this->assertCount(0, $eq->getEndIndexes());
      $this->assertCount(0, $eq->getEndIndexes());

      # check cache
      $this->assertCount(0, $eq->getStartIndexes());
      $this->assertCount(0, $eq->getStartIndexes());
      $this->assertCount(0, $eq->getBlock());

    }

    public function _testExpect() {
      $file = $this->initFileWithCode("<?php
        echo 1+3-5;
        echo 2+4-6;
        echo 1+4;
      ");

      $collection = $file->getCollection();

      $eq = $collection->extendedQuery();
      $eq->strict()->valueIs('echo');
      $eq->expect()->valueIs('-');

      $this->assertCount(2, $eq->getBlock());
    }

    public function _testSearch() {
      $file = $this->initFileWithCode('<?php
         echo 1+5*9; echo 2+4
      ');
      $q = $file->getCollection()->extendedQuery();
      $q->strict()->valueIs('echo');
      $q->search()->valueIs(';');

      $this->assertCount(1, $q->getBlock());
      $code = (string) $q->getBlock()->getFirst();
      $this->assertEquals('echo 1+5*9;', $code);

    }

    public function _testExpectResult() {
      $file = $this->initFileWithCode('<?php
         echo 1+5*9; echo 2+4
      ');
      $q = $file->getCollection()->extendedQuery();
      $q->strict()->valueIs('echo');
      $q->expect()->valueIs(';');

      $this->assertCount(1, $q->getBlock());
      $code = (string) $q->getBlock()->getFirst();
      $this->assertEquals('echo 1+5*9', $code);
    }

    public function _testExpectFail() {
      $file = $this->initFileWithCode('<?php
         echo 1+5*9; echo 2+4
      ');
      $q = $file->getCollection()->extendedQuery();
      $q->strict()->valueIs('echo');
      $q->expect()->typeIs(T_WHITESPACE);

      $this->assertCount(0, $q->getBlock());
    }

    public function _testSectionFail() {
      $file = $this->initFileWithCode('<?php
         function(){}
         function (){}
      ');
      $q = $file->getCollection()->extendedQuery();
      $q->strict()->valueIs('function');
      $q->section('(', ')');

      $this->assertCount(2, $q->getBlock());
    }

    public function _testMoreQueriesThanTokens() {
      $file = $this->initFileWithCode('<?php
         echo
      ');
      $q = $file->getCollection()->extendedQuery();
      $q->strict()->valueLike('!.+!');
      $q->strict()->typeIs(T_WHITESPACE);
      $q->strict()->typeIs(T_ECHO);
      $q->strict()->typeIs(T_WHITESPACE);
      $q->strict()->valueLike('!.+!');

      $this->assertCount(0, $q->getBlock());

      $file = $this->initFileWithCode('<?php
         echo 1;
         echo');
      $q = $file->getCollection()->extendedQuery();
      $q->strict()->typeIs(T_ECHO);
      $q->strict()->typeIs(T_WHITESPACE);
      $q->strict()->valueLike('!.+!');

      $this->assertCount(1, $q->getBlock());

    }

  }
