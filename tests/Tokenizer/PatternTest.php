<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Exception\Exception;
  use Funivan\PhpTokenizer\Pattern\PatternMatcher;
  use Funivan\PhpTokenizer\Pattern\Patterns\ClassPattern;
  use Funivan\PhpTokenizer\Pattern\Patterns\MethodPattern;
  use Funivan\PhpTokenizer\QuerySequence\QuerySequence;
  use Funivan\PhpTokenizer\Strategy\Strict;
  use Test\Funivan\PhpTokenizer\MainTestCase;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 6/26/15
   */
  class PatternTest extends MainTestCase {

    /**
     * Prototype for new version
     */
    public function testWithCallbackPattern() {
      $code = '<?php class A { public $user = null; }';
      $tokensChecker = new PatternMatcher(Collection::createFromString($code));

      $tokensChecker->apply(function (QuerySequence $processor) {
        $processor->strict('class');
        $processor->strict(T_WHITESPACE);
        $processor->process(Strict::create()->valueLike("!.*!"));
        $processor->possible(T_WHITESPACE);
        $body = $processor->section('{', '}');
        if ($processor->isValid()) {
          return $body->extractItems(1, -1);
        }
        return null;
      });

      static::assertCount(1, $tokensChecker->getCollections());
    }


    /**
     * @return array
     */
    public function getStrictSectionAndSequencePatternDataProvider() {
      return [
        [
          '(preg_match("!a!", $b)) $this->item["test"] = 123;',
          true,
        ],
        [
          '(preg_match (         "!a!", $b))               $this -> item [ " test " ] = 123;',
          true,
        ],
        [
          '(preg_match ($b))$this->item[$key] = 123;',
          false,
        ],

      ];
    }


    /**
     * @dataProvider getStrictSectionAndSequencePatternDataProvider
     * @param string $data
     * @param boolean $expectResult
     */
    public function testStrictSectionAndSequencePattern($data, $expectResult) {

      $code = '<?php 
      ' . $data;
      $tokensChecker = new PatternMatcher(Collection::createFromString($code));
      $result = [];
      $tokensChecker->apply(function (QuerySequence $q) use (&$result) {
        $q->setSkipWhitespaces(true);
        $start = $q->strict('preg_match');
        $q->section('(', ')');
        $sequence = $q->sequence([
          ')',
          '$this',
          '->',
          'item',
          '[',
          T_CONSTANT_ENCAPSED_STRING,
          ']',
          '=',
        ]);

        if ($q->isValid()) {
          $result = $q->getCollection()->extractByTokens($start, $sequence->getLast());
        }

      });

      static::assertEquals($expectResult, !empty($result));
    }


    public function testWithClassPattern() {
      $code = '<?php class A { public $user = null; } class customUser { }';
      $tokensChecker = new PatternMatcher(Collection::createFromString($code));
      $tokensChecker->apply(new ClassPattern());

      static::assertCount(2, $tokensChecker->getCollections());

      $tokensChecker = new PatternMatcher(Collection::createFromString($code));
      $classPattern = new ClassPattern();
      $classPattern->withName('B');
      $tokensChecker->apply($classPattern);

      static::assertCount(0, $tokensChecker->getCollections());

      $tokensChecker = new PatternMatcher(Collection::createFromString($code));
      $classPattern = new ClassPattern();
      $classPattern->withName('A');
      $tokensChecker->apply($classPattern);

      static::assertCount(1, $tokensChecker->getCollections());

      $tokensChecker = new PatternMatcher(Collection::createFromString($code));
      $classPattern = new ClassPattern();
      $classPattern->withName('customUser');
      $tokensChecker->apply($classPattern);

      static::assertCount(1, $tokensChecker->getCollections());
    }


    public function testWithNestedPatterns() {
      # find class with property
      $code = '<?php class A { public $user = null; static $name;} class customUser { $value; }';
      $tokensChecker = new PatternMatcher(Collection::createFromString($code));
      $tokensChecker
        ->apply(new ClassPattern())
        ->apply(function (QuerySequence $p) {
          $p->process(Strict::create()->valueIs(
            [
              'public',
              'protected',
              'private',
              'static',
            ]
          ));
          $p->strict(T_WHITESPACE);
          $name = $p->strict(T_VARIABLE);
          if ($p->isValid()) {
            return new Collection([$name]);
          }
          return null;
        });

      $collections = $tokensChecker->getCollections();
      static::assertCount(2, $collections);

      static::assertEquals('$user', (string) $collections[0]);
      static::assertEquals('$name', (string) $collections[1]);

    }


    /**
     * @expectedException Exception
     */
    public function testInvalidPatternResult() {
      $tokensChecker = new PatternMatcher(Collection::createFromString('<?php echo 1;'));
      /** @noinspection PhpUnusedParameterInspection */
      $tokensChecker->apply(function (QuerySequence $process) {
        return new \stdClass();
      });

    }


    /**
     * @expectedException Exception
     */
    public function testInvalidPatternResultArray() {
      $tokensChecker = new PatternMatcher(Collection::createFromString('<?php echo 1;'));
      /** @noinspection PhpUnusedParameterInspection */
      $tokensChecker->apply(function (QuerySequence $process) {
        return [new \stdClass()];
      });

    }


    /**
     * @requires PHP 5.5
     */
    public function testFluentInterface() {

      $code = '<?php 
      class UsersController extends Base { 
        public function test(){
          header("123");
        }
      }
      
      ';
      $collection = Collection::createFromString($code);
      $tokensChecker = new PatternMatcher($collection);
      $tokensChecker->apply(
        (new ClassPattern())->withName('UsersController')
      )->apply(function (QuerySequence $q) {
        $function = $q->strict('header');
        $q->strict('(');
        if ($q->isValid()) {
          $function->setValue('$this->response()->redirect');
        }
      });

      static::assertContains('$this->response()->redirect("123")', (string) $collection);

    }


    /**
     * @expectedException \Funivan\PhpTokenizer\Exception\Exception
     */
    public function testPatternWithNullResult() {
      $code = '<?php 
      class UsersController extends Base { 
        public function test(){
          header("123");
        }
      }
      
      ';
      $collection = Collection::createFromString($code);
      $tokensChecker = new PatternMatcher($collection);
      $tokensChecker->apply(function (QuerySequence $q) {
        $q->setSkipWhitespaces(true);
        return [];
      });

    }


    public function testCombinedPatterns() {
      $code = '<?php
      
      class Logger {
         public function log($message){
          echo $message;
         }
      }
      
      class User { 
        public function getName(){
          return $this->name;
        }
        public function getPassword(){
          return $this->password;
        }
      }
      
      
      ';

      $collection = Collection::createFromString($code);
      $tokensChecker = new PatternMatcher($collection);

      $tokensChecker->apply(new ClassPattern())
        ->apply(new MethodPattern());

      $collections = $tokensChecker->getCollections();
      static::assertCount(3, $collections);
      static::assertContains('echo $message', (string) $collections[0]);
      static::assertContains('return $this->name', (string) $collections[1]);
      static::assertContains('return $this->password', (string) $collections[2]);

    }

  }