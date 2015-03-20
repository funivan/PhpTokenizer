<?

  namespace Test\Funivan\PhpTokenizer\Extractor;

  class TokenSequenceExtractorTest extends \Test\Funivan\PhpTokenizer\Main {

    public function testWithChildExtractor() {
      $file = $this->initFileWithCode("<?php
        header('Location: http://funivan.com');
        header(123);
        header ('test:test');
      ");


      $sequence = new \Funivan\PhpTokenizer\Extractor\TokenSequence();
      $sequence->strict()->valueIs('header');
      $sequence->possible()->typeIs(T_WHITESPACE);
      $sequence->strict()->valueIs('(');
      $sequence->strict()->valueLike('!.*!');
      $sequence->strict()->valueIs(')');


      $stringSequence = new \Funivan\PhpTokenizer\Extractor\TokenSequence();
      $stringSequence->strict()->typeIs(T_CONSTANT_ENCAPSED_STRING);
      $sequence->with($stringSequence, 'test');

      $block = $sequence->extract($file->getCollection());
      $this->assertCount(2, $block);
      $this->assertEquals("header('Location: http://funivan.com')", $block[0]);
      $this->assertEquals("header ('test:test')", $block[1]);

      $block = $sequence->extract($file->getCollection(), 'test');
      $this->assertCount(2, $block);
      $this->assertEquals("'Location: http://funivan.com'", $block[0]);
      $this->assertEquals("'test:test'", $block[1]);

      unlink($file->getPath());

    }

  }
