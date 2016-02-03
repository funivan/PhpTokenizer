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
      $collections = $tokensChecker->getCollections();
      $this->assertCount(2, $collections);
    }


    public function testNameIs() {

      $tokensChecker = new Pattern($this->getDemoCollection());
      $checker = new ClassPattern();
      $checker->withName('B');
      $tokensChecker->apply($checker);
      $this->assertCount(1, $tokensChecker->getCollections());

      $tokensChecker = new Pattern($this->getDemoCollection());
      $checker = new ClassPattern();
      $checker->withName(Strict::create()->valueIs('b'));
      $tokensChecker->apply($checker);
      $this->assertCount(0, $tokensChecker->getCollections());

      $tokensChecker = new Pattern($this->getDemoCollection());
      $checker = new ClassPattern();
      $checker->withName(Strict::create()->valueIs('B'));
      $tokensChecker->apply($checker);
      $this->assertCount(1, $tokensChecker->getCollections());

    }


    public function testNameCustomCheck() {
      $tokensChecker = new Pattern($this->getDemoCollection());
      $checker = new ClassPattern();
      $checker->withName(Strict::create()->valueLike('![a-z]+!i'));
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


    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidNameCondition() {
      $pattern = new ClassPattern();
      $pattern->withName(new \stdClass());

    }


    /**
     * @return array
     */
    public function getForSimpleDetectionProvider() {
      return [
        [
          0, '<?php
      
      function test(){
            
      }
      ',
        ],
        [
          1, '<?php 
        class abc{}
        function a(){
        }
        ',
        ],

        [
          2, '<?php 
        class abc{}
        
        class dfg{
          function a(){
          
          }
        
        }
        ',
        ],
      ];
    }


    /**
     *
     * @dataProvider getForSimpleDetectionProvider
     * @param int $expectItems
     * @param string $code
     */
    public function testSimpleDetection($expectItems, $code) {


      $collection = Collection::createFromString($code);
      $checker = new Pattern($collection);
      $pattern = new ClassPattern();

      $checker->apply($pattern);

      $this->assertCount($expectItems, $checker->getCollections());
    }


    public function testOutputFullClass() {
      $tokensChecker = new Pattern($this->getDemoCollection());
      $tokensChecker->apply((new ClassPattern())->outputFull());

      $this->assertCount(2, $tokensChecker->getCollections());

      $this->assertStringStartsWith('class B {', (string) $tokensChecker->getCollections()[0]);
    }


    public function testOutputFullClassWithAllKeywords() {
      $checker = new Pattern(Collection::createFromString('<?php abstract class B{} final class A {}'));

      $checker->apply((new ClassPattern())->outputFull());

      $this->assertCount(2, $checker->getCollections());
      $this->assertStringStartsWith('abstract class B{', (string) $checker->getCollections()[0]);
      $this->assertStringStartsWith('final class A ', (string) $checker->getCollections()[1]);
      
      
    }


  }
