<?

  namespace Test\Funivan\PhpTokenizer\Tokenizer\Query\QueryProcessor;

  class StrictTest extends \Test\Funivan\PhpTokenizer\Main {

    public function testExtractSlice() {
      $file = $this->initFileWithCode("<?php  
      header(); 
      header('test'); 
      header (); 
      
      ");

      $sequence = new \Funivan\PhpTokenizer\Extractor\TokenSequence();
      $sequence->strict()->valueIs('header');
      $sequence->strict()->valueIs('(');
      $block = $sequence->extract($file->getCollection());
      $this->assertCount(2, $block);
      $firstCode = $block->getFirst()->assemble();
      $this->assertEquals('header(', $firstCode);

      $sequence = new \Funivan\PhpTokenizer\Extractor\TokenSequence();
      $sequence->strict()->valueIs('header');
      $sequence->strict()->valueIs('(');
      $sequence->strict()->valueIs(')');
      $block = $sequence->extract($file->getCollection());
      $this->assertCount(1, $block);
      $firstCode = $block->getFirst()->assemble();
      $this->assertEquals('header()', $firstCode);
      unlink($file->getPath());
    }

    public function testExtractWithSingleCondition() {
      $file = $this->initFileWithCode("<?php
        header('Location: http://funivan.com');
        header();
        header ('test:test');
      ");

      $sequence = new \Funivan\PhpTokenizer\Extractor\TokenSequence();
      $sequence->strict()->valueIs('header');

      $this->assertCount(3, $sequence->extract($file->getCollection()));


      unlink($file->getPath());
    }

    public function testExtractWithMultipleCondition() {
      $file = $this->initFileWithCode("<?php
        header('Location: http://funivan.com');
        header();
        header ('test:test');");

      $sequence = new \Funivan\PhpTokenizer\Extractor\TokenSequence();
      $sequence->strict()->valueIs('header');
      $sequence->strict()->valueIs('(');
      $this->assertCount(2, $sequence->extract($file->getCollection()));


      $sequence = new \Funivan\PhpTokenizer\Extractor\TokenSequence();
      $sequence->strict()->valueIs('header');
      $sequence->strict()->valueIs(['(', ' ']);
      $block = $sequence->extract($file->getCollection());
      $this->assertCount(3, $block);

      $this->assertEquals('header ', $block->getLast()->assemble());


      $sequence = new \Funivan\PhpTokenizer\Extractor\TokenSequence();
      $sequence->strict()->valueIs('header');
      $sequence->strict()->valueIs('(');
      $block = $sequence->extract($file->getCollection());
      $this->assertCount(2, $block);


      $sequence = new \Funivan\PhpTokenizer\Extractor\TokenSequence();
      $sequence->strict()->valueIs(')');
      $sequence->strict()->valueIs(';');
      $sequence->strict()->typeIs(T_WHITESPACE);
      $this->assertCount(2, $sequence->extract($file->getCollection()));


      unlink($file->getPath());
    }

  }
