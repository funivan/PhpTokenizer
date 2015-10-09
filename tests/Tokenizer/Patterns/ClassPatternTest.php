<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer\Patterns;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Pattern\Pattern;
  use Funivan\PhpTokenizer\Pattern\Patterns\ClassPattern;
  use Funivan\PhpTokenizer\Strategy\Strict;
  use Test\Funivan\PhpTokenizer\MainTestCase;

  /**
   *
   * @package Test\Funivan\PhpTokenizer\Tokenizer\Patterns
   */
  class ClassPatternTest extends MainTestCase {


    public function testClassDetect() {

      $tokensChecker = new Pattern($this->getDemoCollection());
      $tokensChecker->apply((new ClassPattern()));
      $this->assertCount(2, $tokensChecker->getCollections());
    }


    public function testNameIs() {

      $tokensChecker = new Pattern($this->getDemoCollection());
      $checker = new ClassPattern();
      $checker->nameIs('B');
      $tokensChecker->apply($checker);
      $this->assertCount(1, $tokensChecker->getCollections());

      $tokensChecker = new Pattern($this->getDemoCollection());
      $checker = new ClassPattern();
      $checker->nameIs(Strict::create()->valueIs('b'));
      $tokensChecker->apply($checker);
      $this->assertCount(0, $tokensChecker->getCollections());

      $tokensChecker = new Pattern($this->getDemoCollection());
      $checker = new ClassPattern();
      $checker->nameIs(Strict::create()->valueIs('B'));
      $tokensChecker->apply($checker);
      $this->assertCount(1, $tokensChecker->getCollections());

    }


    public function testNameCustomCheck() {
      $tokensChecker = new Pattern($this->getDemoCollection());
      $checker = new ClassPattern();
      $checker->whereName(Strict::create()->valueLike('![a-z]+!i'));
      $tokensChecker->apply($checker);

      $this->assertCount(2, $tokensChecker->getCollections());

    }


    /**
     * @return Collection
     */
    private function getDemoCollection() {
      $collection = Collection::createFromString('<?php 
      class B {
      
      }
      class UsersController extends Base { 
        public function test(){
          header("123");
        }
      }
      
      function test(){
            
      }
      ');
      return $collection;
    }


  }
