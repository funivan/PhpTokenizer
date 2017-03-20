<?php

  declare(strict_types=1);

  namespace Test\Funivan\PhpTokenizer\Tokenizer\Strategy;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\QuerySequence\QuerySequence;
  use Funivan\PhpTokenizer\Strategy\Search;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 4/17/15
   */
  class SearchTest extends \PHPUnit_Framework_TestCase {

    public function testSearchDefault() {

      $code = '<?php 
      
      echo $a;
      echo 1 . $a;
      
      
      ';

      $collection = Collection::createFromString($code);
      $linesWithEcho = [];

      foreach ($collection as $index => $token) {
        $q = new QuerySequence($collection, $index);
        $start = $q->strict('echo');
        $end = $q->search(';');

        if ($q->isValid()) {
          $linesWithEcho[] = $collection->extractByTokens($start, $end);
        }
      }

      static::assertCount(2, $linesWithEcho);
      static::assertEquals('echo $a;', $linesWithEcho[0]);
      static::assertEquals('echo 1 . $a;', $linesWithEcho[1]);

    }


    /**
     *
     */
    public function testBackwardSearch() {

      $code = '<?php 
      
      
      echo $name;
      echo $userName;
      
      
      ';

      $collection = Collection::createFromString($code);

      $linesWithEcho = [];

      foreach ($collection as $index => $token) {
        $q = new QuerySequence($collection, $index);

        $q->strict('echo');
        $q->search(';');
        $variable = $q->search(T_VARIABLE, Search::BACKWARD);
        if ($q->isValid()) {
          $linesWithEcho[] = $variable;
        }
      }

      static::assertCount(2, $linesWithEcho);
      static::assertEquals('$name', (string) $linesWithEcho[0]);
      static::assertEquals('$userName', (string) $linesWithEcho[1]);

    }


    /**
     * @expectedException \Funivan\PhpTokenizer\Exception\InvalidArgumentException
     */
    public function testInvalidDirection() {
      Search::create()->setDirection(null);
    }


    public function testSearchBackward() {

      $code = '<?php 
      
      echo $name;
      echo $userName;
      echo (string) $userName;
      
      
      ';

      $collection = Collection::createFromString($code);

      $linesWithEcho = [];

      foreach ($collection as $index => $item) {
        $q = new QuerySequence($collection, $index);
        $q->strict('echo');
        $q->search(';');
        $variable = $q->search('(string)', Search::BACKWARD);
        if ($q->isValid()) {
          $linesWithEcho[] = $variable;
        }
      }

      static::assertCount(1, $linesWithEcho);
    }

  }
