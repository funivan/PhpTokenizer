<?php

  namespace Test\Funivan\PhpTokenizer\Extractor;

  use Funivan\PhpTokenizer\Extractor\Extractor;
  use Funivan\PhpTokenizer\Extractor\TokenSequence;
  use Funivan\PhpTokenizer\Query\Query;
  use Funivan\PhpTokenizer\Query\QueryProcessor\Strict;

  class TokenSequenceExtractorTest extends \Test\Funivan\PhpTokenizer\Main {

    public function testWithChildExtractor() {
      $file = $this->initFileWithCode("<?php
        header('Location: http://funivan.com');
        header(123);
        header ('test:test');
      ");

      $sequence = new TokenSequence();
      $sequence->strict()->valueIs('header');
      $sequence->possible()->typeIs(T_WHITESPACE);
      $sequence->strict()->valueIs('(');
      $sequence->strict()->valueLike('!.*!');
      $sequence->strict()->valueIs(')');

      $extractor = new Extractor($file->getCollection(), $sequence);
      $blocks = $extractor->fetchBlocks();
      $this->assertCount(3, $blocks);
      $this->assertEquals("header('Location: http://funivan.com')", $blocks[0]);
      $this->assertEquals("header(123)", $blocks[1]);
      $this->assertEquals("header ('test:test')", $blocks[2]);

      $stringSequence = new TokenSequence();
      $stringSequence->strict()->typeIs(T_CONSTANT_ENCAPSED_STRING);
      $sequence->with($stringSequence, 'test');

      $blocks = $extractor->fetchBlocks();
      $this->assertCount(2, $blocks);
      $this->assertEquals("header('Location: http://funivan.com')", $blocks[0]);
      $this->assertEquals("header ('test:test')", $blocks[1]);

      unlink($file->getPath());
    }

    public function _testWithMultipleChildExtractor() {
      $file = $this->initFileWithCode("<?php
        header('Location: http://funivan.com');
        header(123);
        header ('test:test');
      ");

      $collection = $file->getCollection();

      $sequence = new TokenSequence('f');
      $sequence->strict()->valueIs('header');
      $sequence->possible()->typeIs(T_WHITESPACE);
      $sequence->strict()->valueIs('(');
      $sequence->strict()->valueLike('!.*!');
      $sequence->strict()->valueIs(')');

      $this->assertCount(3, $sequence->extract($collection));

      $stringExtractor = TokenSequence::create('f2')->addProcessor(new Strict((new Query())->typeIs(T_CONSTANT_ENCAPSED_STRING)));
      $sequence->with($stringExtractor);

      $this->assertCount(2, $sequence->extract($collection));

      $testStringExtractor = TokenSequence::create('f3')->addProcessor(new Strict((new Query())->valueLike('!\:test!')));
      $stringExtractor->with($testStringExtractor);

      $blocks = $sequence->extract($collection, 'f3');

      $this->assertCount(1, $blocks);

      $this->assertEquals("'test:test'", $blocks[0]);

      unlink($file->getPath());
    }


    public function _testWithSingleToken() {
      $file = $this->initFileWithCode("<?php
        header('Location: http://funivan.com');
        header(123);
        header ('test:test');
      ");

      $collection = $file->getCollection()->extractItems(3, 1);

      $sequence = new TokenSequence();
      $sequence->strict()->valueIs('(');

      $ranges = $sequence->getRangeList($collection);

      $this->assertCount(1, $ranges);

    }

    public function testExtractorName() {
      $tokenSequenceFinder = TokenSequence::create("test");
      $this->assertEquals('test', $tokenSequenceFinder->getName());

    }
  }
