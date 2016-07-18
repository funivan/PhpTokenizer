<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer\Strategy;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Pattern\PatternMatcher;
  use Funivan\PhpTokenizer\QuerySequence\QuerySequence;
  use Funivan\PhpTokenizer\Strategy\Section;
  use Funivan\PhpTokenizer\Strategy\Strict;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 4/18/15
   */
  class SectionTest extends \PHPUnit_Framework_TestCase {

    /**
     * @return array
     */
    public function functionCallDataProvider() {
      return [
        [
          'header(123); ',
          'header',
          'header(123)',
        ],
        [
          'echo header          (123, 432); ',
          'header',
          'header          (123, 432)',
        ],

        [
          'echo header          (123, 432); ',
          'header',
          'header          (123, 432)',
        ],

      ];
    }


    /**
     * @dataProvider functionCallDataProvider
     * @param $code
     * @param $functionName
     * @param $expectCode
     */
    public function testFunctionCall($code, $functionName, $expectCode) {
      $code = '<?php ' . $code;

      $collection = Collection::createFromString($code);

      $lines = [];

      foreach ($collection as $index => $token) {
        $q = new QuerySequence($collection, $index);
        $start = $q->strict($functionName);
        $q->possible(T_WHITESPACE);
        $end = $q->section('(', ')');

        if ($q->isValid()) {
          $lines[] = $collection->extractByTokens($start, $end->getLast());
        }
      }

      static::assertCount(1, $lines);
      static::assertEquals($expectCode, $lines[0]);
    }


    public function testWithEmptySection() {
      $code = '<?php 
      
      header(123);
      
      return;
      
      ';

      $collection = Collection::createFromString($code);
      $linesWithEcho = [];

      foreach ($collection as $index => $token) {
        $q = new QuerySequence($collection, $index);
        $start = $q->strict('return');
        $end = $q->section('(', ')');

        if ($q->isValid()) {
          $linesWithEcho[] = $collection->extractByTokens($start, $end->getLast());
        }
      }

      static::assertCount(0, $linesWithEcho);

    }


    public function testWithEmptySectionSearch() {
      $code = '<?php 
      
      header(123);
      
      return;
      
      ';

      $collection = Collection::createFromString($code);


      $linesWithEcho = [];

      foreach ($collection as $index => $token) {
        $q = new QuerySequence($collection, $index);
        $start = $q->strict('return');
        $lastToken = $q->process(Section::create()->setDelimiters('(', ')'));

        if ($q->isValid()) {
          $linesWithEcho[] = $collection->extractByTokens($start, $lastToken);
        }
      }

      static::assertCount(0, $linesWithEcho);

    }


    public function testWithMultipleTokens() {

      $code = '<?php 
      
      class User { 
        abstract function getInfo();

        public function save() {}
      }
      ';

      $collection = Collection::createFromString($code);


      $num = 0;

      (new PatternMatcher($collection))->apply(function (QuerySequence $q) use (&$num) {

        $q->strict(')');
        $q->possible(T_WHITESPACE);
        $q->section('{', '}');

        if ($q->isValid()) {
          $num++;
        }

      });

      static::assertEquals(1, $num);

    }


    /**
     * @return array
     */
    public function functionDetectDataProvider() {
      return [
        [
          function (QuerySequence $q) {
            $q->strict(')');
            $q->possible(T_WHITESPACE);
            $q->section('{', '}');
          },
          2,
        ],
        [
          function (QuerySequence $q) {
            $q->strict(')');
            $q->section('{', '}');
          },
          1,
        ],
        [
          function (QuerySequence $q) {
            $q->setSkipWhitespaces(true);
            $q->strict(')');
            $q->section('{', '}');
          },
          2,
        ],
        [
          function (QuerySequence $q) {
            $q->setSkipWhitespaces(true);
            $q->strict(Strict::create()->valueLike('!^[a-z]+$!i'));
            $q->section('(', ')');
            $q->section('{', '}');
          },
          2,
        ],
        [
          function (QuerySequence $q) {
            $q->strict(Strict::create()->valueLike('!^[a-z]+$!i'));
            $q->section('(', ')');
            $q->section('{', '}');
          },
          0,
        ],
        [
          function (QuerySequence $q) {
            $q->strict(Strict::create()->valueLike('!^[a-z]+$!i'));
            $q->strict(T_WHITESPACE);
            $q->section('(', ')');
            $q->section('{', '}');
          },
          1,
        ],
      ];
    }


    /**
     * @dataProvider functionDetectDataProvider
     * @param callable $callback
     * @param $expectFunctionNum
     */
    public function testFunctionDetect(callable $callback, $expectFunctionNum) {
      $code = '<?php 
      function getInfo ($df){}
      function save() {}
      ';

      $collection = Collection::createFromString($code);


      $num = 0;

      (new PatternMatcher($collection))->apply(function (QuerySequence $q) use ($callback, &$num) {
        $callback($q);

        if ($q->isValid()) {
          $num++;
        }

      });

      static::assertEquals($expectFunctionNum, $num);
    }


    /**
     * @expectedException \Funivan\PhpTokenizer\Exception\InvalidArgumentException
     */
    public function testInvalidSectionStartDefinition() {

      $section = new Section();
      $section->process(new Collection(), 0);

    }


    /**
     * @expectedException \Funivan\PhpTokenizer\Exception\InvalidArgumentException
     */
    public function testInvalidSectionEndDefinition() {

      $section = new Section();
      $section->setStartQuery(new \Funivan\PhpTokenizer\Query\Query());

      $section->process(new Collection(), 0);

    }

  }
