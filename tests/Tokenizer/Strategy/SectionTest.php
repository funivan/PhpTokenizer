<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer\Strategy;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\StreamProcess\DefaultStreamProcess;
  use Funivan\PhpTokenizer\TokenStream;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 4/18/15
   */
  class SectionTest extends \PHPUnit_Framework_TestCase {

    public function testValidSection() {
      $code = '<?php 
      
      header(123);
      if(){
      }
      
      
      ';

      $collection = Collection::initFromString($code);
      $finder = new DefaultStreamProcess($collection);

      $linesWithEcho = array();

      foreach ($finder as $q) {

        $start = $q->strict('header');
        $end = $q->section('(', ')');

        if ($q->isValid()) {
          $linesWithEcho[] = $collection->extractByTokens($start, $end);
        }
      }

      $this->assertCount(1, $linesWithEcho);

    }

    public function testWithEmptySection() {
      $code = '<?php 
      
      header(123);
      
      return;
      
      ';

      $collection = Collection::initFromString($code);
      $finder = new DefaultStreamProcess($collection);

      $linesWithEcho = array();

      foreach ($finder as $q) {

        $start = $q->strict('return');
        $end = $q->section('(', ')');

        if ($q->isValid()) {
          $linesWithEcho[] = $collection->extractByTokens($start, $end);
        }
      }

      $this->assertCount(0, $linesWithEcho);

    }

    /**
     * @expectedException \Funivan\PhpTokenizer\Exception\InvalidArgumentException
     */
    public function testInvalidSectionStartDefinition() {

      $section = new \Funivan\PhpTokenizer\Strategy\Section();
      $section->process(new Collection(), 0);

    }

    /**
     * @expectedException \Funivan\PhpTokenizer\Exception\InvalidArgumentException
     */
    public function testInvalidSectionEndDefinition() {

      $section = new \Funivan\PhpTokenizer\Strategy\Section();
      $section->setStartQuery(new \Funivan\PhpTokenizer\Query\Query());

      $section->process(new Collection(), 0);

    }

  }
