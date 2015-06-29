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
  class TokensCheckerTest extends MainTestCase {

    public function _testQuerySequence() {
      $code = '<?php class A { public $user = null; }';
      $collection = Collection::initFromString($code);
      //
      //foreach ($collection as $index => $token) {
      //  $querySequence = new QuerySequence($collection, $index);
      //  $querySequence->st
      //  if ($patternResult === null) {
      //    continue;
      //  }
      //
      //  $result[] = $patternResult;
      //}

    }

    /**
     * Prototype for new version
     */
    public function testWithCallbackPattern() {
      $code = '<?php class A { public $user = null; }';
      $tokensChecker = new Pattern(Collection::initFromString($code));

      $tokensChecker->apply(function (QuerySequence $processor) {
        $processor->strict('class');
        $processor->process(Strict::create()->valueLike("!.*!"));
        $body = $processor->section('{', '}');
        if ($processor->isValid()) {
          return $body->extractItems(1, -1);
        }
      });

      $this->assertCount(1, $tokensChecker->getCollections());
    }

    public function testWithClassPattern() {
      $code = '<?php class A { public $user = null; } class customUser { }';
      $tokensChecker = new Pattern(Collection::initFromString($code));
      $tokensChecker->apply(new ClassPattern());


      $this->assertCount(2, $tokensChecker->getCollections());

      $tokensChecker = new Pattern(Collection::initFromString($code));
      $classPattern = new ClassPattern();
      $classPattern->nameIs('B');
      $tokensChecker->apply($classPattern);

      $this->assertCount(0, $tokensChecker->getCollections());

      $tokensChecker = new Pattern(Collection::initFromString($code));
      $classPattern = new ClassPattern();
      $classPattern->nameIs('A');
      $tokensChecker->apply($classPattern);

      $this->assertCount(1, $tokensChecker->getCollections());

      $tokensChecker = new Pattern(Collection::initFromString($code));
      $classPattern = new ClassPattern();
      $classPattern->nameIs('customUser');
      $tokensChecker->apply($classPattern);

      $this->assertCount(1, $tokensChecker->getCollections());
    }


    public function _testWithNestedPatterns() {
      # find class with property 
      $code = '<?php class A { public $user = null; static $name;} class customUser { $value; }';
      $tokensChecker = new Pattern(Collection::initFromString($code));
      $tokensChecker->apply(new ClassPattern())
        ->apply(function (QuerySequence $process) {

          $result = array();
          foreach ($process as $p) {

            $p->process(Strict::create()->valueIs(
              array(
                'public',
                'protected',
                'private',
                'static',
              )
            ));

            $name = $p->strict(T_VARIABLE);
            if ($p->isValid()) {
              $result[] = new Collection(array($name));
            }

          }

          return $result;
        });

      $collections = $tokensChecker->getCollections();
      $this->assertCount(2, $collections);

      $this->assertEquals('$user', (string) $collections[0]);
      $this->assertEquals('$name', (string) $collections[1]);

    }

    /**
     * @expectedException Exception
     */
    public function _testInvalidPatternResult() {
      $tokensChecker = new Pattern(Collection::initFromString('<?php echo 1;'));
      /** @noinspection PhpUnusedParameterInspection */
      $tokensChecker->apply(function (QuerySequence $process) {
        return new \stdClass();
      });

    }

    /**
     * @expectedException Exception
     */
    public function _testInvalidPatternResultArray() {
      $tokensChecker = new Pattern(Collection::initFromString('<?php echo 1;'));
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
      $collection = Collection::initFromString($code);
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

  }