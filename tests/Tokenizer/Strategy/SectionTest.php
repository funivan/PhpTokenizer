<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer\Strategy;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\TokenStream;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 4/18/15
   */
  class SectionTest extends \PHPUnit_Framework_TestCase {

    public function testSection() {
      $code = '<?php 
      
      header(123);
      if(){
      }
      
      
      ';

      $collection = Collection::initFromString($code);
      $finder = new TokenStream($collection);

      $linesWithEcho = array();

      while ($q = $finder->iterate()) {

        $start = $q->strict('header');
        $end = $q->section('(', ')');

        if ($q->isValid()) {
          $linesWithEcho[] = $collection->extractByTokens($start, $end);
        }
      }

      $this->assertCount(1, $linesWithEcho);

    }

  }
