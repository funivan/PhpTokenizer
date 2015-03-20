<?

  namespace Test\Funivan\PhpTokenizer\Tokenizer\Query\QueryProcessor;

  use Funivan\PhpTokenizer\Extractor\TokenSequence;

  class PossibleTest extends \Test\Funivan\PhpTokenizer\Main {

    public function testPossibleInTheMiddle() {


      $file = $this->initFileWithCode("
      <?php
        header('Location: http://funivan.com');
        header();
        header ('test:test');
      ");

      $sequence = new TokenSequence();
      $sequence->strict()->valueIs('header');
      $sequence->possible()->typeIs(T_WHITESPACE);
      $sequence->strict()->valueIs('(');

      $this->assertCount(3, $sequence->extract($file->getCollection()));


      unlink($file->getPath());

    }

    public function testPossibleEnd() {


      $file = $this->initFileWithCode("
      <?php
        header('Location: http://funivan.com');
        header();
        header ('test:test');
      ");

      $sequence = new TokenSequence();
      $sequence->strict()->valueIs('header');
      $sequence->possible()->typeIs(T_WHITESPACE);


      $this->assertCount(3, $sequence->extract($file->getCollection()));

      unlink($file->getPath());
    }


    public function testStart() {


      $file = $this->initFileWithCode("
      <?php
        header('Location: http://funivan.com');
        header();
        user('test:test');
      ");

      $sequence = new TokenSequence();
      $sequence->possible()->valueIs('header');
      $sequence->strict()->valueIs('(');

      $block = $sequence->extract($file->getCollection());
      $this->assertCount(5, $block);
      $this->assertEquals('header(', $block[0]->assemble());
      $this->assertEquals('(', $block[1]->assemble());
      $this->assertEquals('header(', $block[2]->assemble());
      $this->assertEquals('(', $block[3]->assemble());
      $this->assertEquals('(', $block[4]->assemble());

      unlink($file->getPath());
      
    }

  }
