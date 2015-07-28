<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Exception\Exception;
  use Funivan\PhpTokenizer\Pattern\Pattern;
  use Funivan\PhpTokenizer\Pattern\Patterns\ClassPattern;
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
      $tokensChecker = new Pattern(Collection::createFromString($code));

      $tokensChecker->apply(function (QuerySequence $processor) {
        $processor->strict('class');
        $processor->process(Strict::create()->valueLike("!.*!"));
        $body = $processor->section('{', '}');
        if ($processor->isValid()) {
          return $body->extractItems(1, -1);
        }
        return null;
      });

      $this->assertCount(1, $tokensChecker->getCollections());
    }

    public function getStrictSectionAndSequencePatternDataProvider() {
      return array(
        array(
          '(preg_match("!a!", $b)) $this->item["test"] = 123;',
          true,
        ),
        array(
          '(preg_match (         "!a!", $b))               $this -> item [ " test " ] = 123;',
          true,
        ),
        array(
          '(preg_match ($b))$this->item[$key] = 123;',
          false,
        ),

      );
    }

    /**
     * @dataProvider getStrictSectionAndSequencePatternDataProvider
     * @param string $data
     * @param boolean $expectResult
     */
    public function testStrictSectionAndSequencePattern($data, $expectResult) {

      $code = '<?php 
      ' . $data;
      $tokensChecker = new Pattern(Collection::createFromString($code));
      $result = array();
      $tokensChecker->apply(function (QuerySequence $q) use (&$result) {
        $q->setSkipWhitespaces(true);
        $start = $q->strict('preg_match');
        $q->section('(', ')');
        $sequence = $q->sequence(array(
          ')',
          '$this',
          '->',
          'item',
          '[',
          T_CONSTANT_ENCAPSED_STRING,
          ']',
          '=',
        ));

        if ($q->isValid()) {
          $result = $q->getCollection()->extractByTokens($start, $sequence->getLast());
        }

      });

      $this->assertEquals($expectResult, !empty($result));
    }


    public function testWithClassPattern() {
      $code = '<?php class A { public $user = null; } class customUser { }';
      $tokensChecker = new Pattern(Collection::createFromString($code));
      $tokensChecker->apply(new ClassPattern());

      $this->assertCount(2, $tokensChecker->getCollections());

      $tokensChecker = new Pattern(Collection::createFromString($code));
      $classPattern = new ClassPattern();
      $classPattern->nameIs('B');
      $tokensChecker->apply($classPattern);

      $this->assertCount(0, $tokensChecker->getCollections());

      $tokensChecker = new Pattern(Collection::createFromString($code));
      $classPattern = new ClassPattern();
      $classPattern->nameIs('A');
      $tokensChecker->apply($classPattern);

      $this->assertCount(1, $tokensChecker->getCollections());

      $tokensChecker = new Pattern(Collection::createFromString($code));
      $classPattern = new ClassPattern();
      $classPattern->nameIs('customUser');
      $tokensChecker->apply($classPattern);

      $this->assertCount(1, $tokensChecker->getCollections());
    }


    public function testWithNestedPatterns() {
      # find class with property 
      $code = '<?php class A { public $user = null; static $name;} class customUser { $value; }';
      $tokensChecker = new Pattern(Collection::createFromString($code));
      $tokensChecker
        ->apply(new ClassPattern())
        ->apply(function (QuerySequence $p) {
          $p->process(Strict::create()->valueIs(
            array(
              'public',
              'protected',
              'private',
              'static',
            )
          ));
          $p->strict(T_WHITESPACE);
          $name = $p->strict(T_VARIABLE);
          if ($p->isValid()) {
            return new Collection(array($name));
          }
          return null;
        });

      $collections = $tokensChecker->getCollections();
      $this->assertCount(2, $collections);

      $this->assertEquals('$user', (string) $collections[0]);
      $this->assertEquals('$name', (string) $collections[1]);

    }

    /**
     * @expectedException Exception
     */
    public function testInvalidPatternResult() {
      $tokensChecker = new Pattern(Collection::createFromString('<?php echo 1;'));
      /** @noinspection PhpUnusedParameterInspection */
      $tokensChecker->apply(function (QuerySequence $process) {
        return new \stdClass();
      });

    }

    /**
     * @expectedException Exception
     */
    public function testInvalidPatternResultArray() {
      $tokensChecker = new Pattern(Collection::createFromString('<?php echo 1;'));
      /** @noinspection PhpUnusedParameterInspection */
      $tokensChecker->apply(function (QuerySequence $process) {
        return array(new \stdClass());
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
      $tokensChecker = new Pattern($collection);
      $tokensChecker->apply(
        (new ClassPattern())->nameIs('UsersController')
      )->apply(function (QuerySequence $q) {
        $function = $q->strict('header');
        $q->strict('(');
        if ($q->isValid()) {
          $function->setValue('$this->response()->redirect');
        }
      });

      $this->assertContains('$this->response()->redirect("123")', (string) $collection);

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
      $tokensChecker = new Pattern($collection);
      $tokensChecker->apply(function (QuerySequence $q) {
        $q->setSkipWhitespaces(true);
        return array();
      });

    }

  }