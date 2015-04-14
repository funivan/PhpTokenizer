<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer\Query\QueryProcessor;

  use Funivan\PhpTokenizer\Strategy\Move;

  /**
   *
   * @package Test\Funivan\PhpTokenizer\Tokenizer\Query\Strategy
   */
  class MoveTest extends \Test\Funivan\PhpTokenizer\Main {

    public function testExtractWithSingleMove() {
      $file = $this->initFileWithCode("<?php
        header();
      ");

      $sequence = new \Funivan\PhpTokenizer\Extractor\TokenSequence();
      $sequence->strict()->valueIs('header');
      $sequence->strict()->valueIs('(');
      $sequence->strict()->valueIs(')');
      $sequence->move(Move::DIRECTION_FORWARD, 1);

      $block = $sequence->extract($file->getCollection());

      $this->assertCount(1, $block);

      unlink($file->getPath());
    }

    public function testSimpleBack() {
      $file = $this->initFileWithCode("<?php
        header();
      ");

      $sequence = new \Funivan\PhpTokenizer\Extractor\TokenSequence();
      $sequence->strict()->valueIs('header');
      $sequence->strict()->valueIs('(');
      $sequence->strict()->valueIs(')');
      $sequence->move(Move::DIRECTION_BACK, 1);

      $block = $sequence->extract($file->getCollection());

      $this->assertCount(1, $block);
      $this->assertEquals('header(', $block->getFirst()->assemble());

      unlink($file->getPath());
    }

    public function testForwardWithMultipleSteps() {
      $file = $this->initFileWithCode("<?php
        header();
        header ('test:test');");

      $sequence = new \Funivan\PhpTokenizer\Extractor\TokenSequence();
      $sequence->strict()->valueIs('header');
      $sequence->move(Move::DIRECTION_FORWARD, 2);

      $block = $sequence->extract($file->getCollection());
      $this->assertCount(2, $block);
      $this->assertEquals('header()', $block[0]->assemble());
      $this->assertEquals('header (', $block[1]->assemble());


      $sequence = new \Funivan\PhpTokenizer\Extractor\TokenSequence();
      $sequence->strict()->valueIs('header');
      $sequence->move(Move::DIRECTION_FORWARD, 2);
      $sequence->strict()->valueIs(';');

      $block = $sequence->extract($file->getCollection());
      $this->assertCount(1, $block);
      $this->assertEquals('header();', $block[0]->assemble());


      unlink($file->getPath());
    }

    public function testWithMultipleConditions() {
      $file = $this->initFileWithCode("<?php
        header(123);
        header(DATA);
        ");

      $sequence = new \Funivan\PhpTokenizer\Extractor\TokenSequence();
      $sequence->strict()->valueIs('header');
      $sequence->move(Move::DIRECTION_FORWARD, 2);
      $sequence->strict()->valueIs(')');

      $block = $sequence->extract($file->getCollection());
      $this->assertCount(2, $block);
      $this->assertEquals('header(123)', $block[0]->assemble());

      unlink($file->getPath());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWithInvalidDirection() {
      new Move(null, 1);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWithInvalidSteps() {
      new Move(Move::DIRECTION_FORWARD, 'left');
    }

  }
