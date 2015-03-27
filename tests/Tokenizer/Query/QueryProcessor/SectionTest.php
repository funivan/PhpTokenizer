<?

  namespace Test\Funivan\PhpTokenizer\Tokenizer\Query\QueryProcessor;

  use Funivan\PhpTokenizer\Extractor\TokenSequence;
  use Funivan\PhpTokenizer\Query\Query;
  use Funivan\PhpTokenizer\Query\QueryProcessor\Section;

  class SectionTest extends \Test\Funivan\PhpTokenizer\Main {


    public function testAllSections() {

      $file = $this->initFileWithCode("
      <?php
        header();
      ");

      $sequence = new TokenSequence();

      $queryStart = new Query();
      $queryStart->valueIs('(');
      $queryEnd = new Query();
      $queryEnd->valueIs(')');

      $sequence->addProcessor(new Section($queryStart, $queryEnd));

      $block = $sequence->extract($file->getCollection());
      $this->assertCount(1, $block);


      unlink($file->getPath());

    }


    public function testStrictWithSections() {

      $file = $this->initFileWithCode("
      <?php
        header();
        user();
        header ();
      ");

      $sequence = new TokenSequence();

      $queryStart = new Query();
      $queryStart->valueIs('(');
      $queryEnd = new Query();
      $queryEnd->valueIs(')');

      $sequence->strict()->valueIs('header');
      $sequence->addProcessor(new Section($queryStart, $queryEnd));

      $block = $sequence->extract($file->getCollection());
      $this->assertCount(1, $block);

      unlink($file->getPath());

    }

  }