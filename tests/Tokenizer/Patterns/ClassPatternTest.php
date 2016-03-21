<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer\Patterns;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Pattern\PatternMatcher;
  use Funivan\PhpTokenizer\Pattern\Patterns\ClassPattern;
  use Funivan\PhpTokenizer\Strategy\Strict;
  use Test\Funivan\PhpTokenizer\MainTestCase;

  /**
   *
   * @package Test\Funivan\PhpTokenizer\Tokenizer\Patterns
   */
  class ClassPatternTest extends MainTestCase {


    public function testClassDetect() {

      $tokensChecker = new PatternMatcher($this->getDemoCollection());
      $tokensChecker->apply((new ClassPattern()));
      $collections = $tokensChecker->getCollections();
      $this->assertCount(2, $collections);
    }


    public function testNameIs() {

      $tokensChecker = new PatternMatcher($this->getDemoCollection());
      $checker = new ClassPattern();
      $checker->withName('B');
      $tokensChecker->apply($checker);
      $this->assertCount(1, $tokensChecker->getCollections());

      $tokensChecker = new PatternMatcher($this->getDemoCollection());
      $checker = new ClassPattern();
      $checker->withName(Strict::create()->valueIs('b'));
      $tokensChecker->apply($checker);
      $this->assertCount(0, $tokensChecker->getCollections());

      $tokensChecker = new PatternMatcher($this->getDemoCollection());
      $checker = new ClassPattern();
      $checker->withName(Strict::create()->valueIs('B'));
      $tokensChecker->apply($checker);
      $this->assertCount(1, $tokensChecker->getCollections());

    }


    public function testNameCustomCheck() {
      $tokensChecker = new PatternMatcher($this->getDemoCollection());
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
      $checker = new PatternMatcher($collection);
      $pattern = new ClassPattern();

      $checker->apply($pattern);

      $this->assertCount($expectItems, $checker->getCollections());
    }


    public function testOutputFullClass() {
      $tokensChecker = new PatternMatcher($this->getDemoCollection());
      $tokensChecker->apply((new ClassPattern())->outputFull());

      $this->assertCount(2, $tokensChecker->getCollections());

      $this->assertStringStartsWith('class B {', (string) $tokensChecker->getCollections()[0]);
    }


    public function testOutputFullClassWithAllKeywords() {
      $checker = new PatternMatcher(Collection::createFromString('<?php abstract class B{} final class A {}'));

      $checker->apply((new ClassPattern())->outputFull());

      $this->assertCount(2, $checker->getCollections());
      $this->assertStringStartsWith('abstract class B{', (string) $checker->getCollections()[0]);
      $this->assertStringStartsWith('final class A ', (string) $checker->getCollections()[1]);

    }


    public function testOutputBody() {
      $checker = new PatternMatcher(Collection::createFromString('<?php abstract class B{public $a=1;} final class A {}'));

      $checker->apply((new ClassPattern())->outputBody());

      $this->assertCount(2, $checker->getCollections());

      $this->assertStringStartsWith('public $a=1;', (string) $checker->getCollections()[0]);
      $this->assertEmpty((string) $checker->getCollections()[1]);

    }


    public function testSelectWithOrWithoutDocComment() {
      $checker = new PatternMatcher(Collection::createFromString('<?php
      /**
       * description
       */
      abstract class B{public $a=1;} 
      
      
      final class A {}'
      ));

      $checker->apply((new ClassPattern())->outputFull());

      $this->assertCount(2, $checker->getCollections());

      $this->assertStringStartsWith('/**', (string) $checker->getCollections()[0]);
      $this->assertStringStartsWith('final class', (string) $checker->getCollections()[1]);

    }


    public function testSelectWithDocComment() {

      $checker = new PatternMatcher(Collection::createFromString('<?php
      /**
       * description
       */
      abstract class B{public $a=1;} 
      
      final class A {}'
      ));

      $checker->apply((new ClassPattern())->outputFull()->withDocComment());

      $this->assertCount(1, $checker->getCollections());

      $this->assertContains('class B', (string) $checker->getCollections()[0]);

    }


    public function testSelectWithoutDocComment() {

      $checker = new PatternMatcher(Collection::createFromString('<?php
      /**
       * description
       */
      abstract class A{public $a=1;} 
      
      class B{}
      /**
       */final class C{}
       
      final class D {}'
      ));

      $checker->apply((new ClassPattern())->outputFull()->withoutDocComment());


      $this->assertCount(2, $checker->getCollections());
      $this->assertContains('class B', (string) $checker->getCollections()[0]);
      $this->assertContains('final class D', (string) $checker->getCollections()[1]);

    }


    public function testWithModifier(){

      $baseChecker = new PatternMatcher(Collection::createFromString('<?php
      /**
       * description
       */
      abstract class A{public $a=1;} 
      
      class B{}
      /**
       */final class C{}
       
      final class D {}
      final class E {
      }
      '
      ));

      $checker = clone $baseChecker;
      $checker->apply((new ClassPattern())->outputFull()->withModifier('abstract'));
      $this->assertCount(1, $checker->getCollections());
      $this->assertContains('/**', (string) $checker->getCollections()[0]);

      $checker = clone $baseChecker;
      $checker->apply((new ClassPattern())->outputFull()->withModifier('final'));
      $this->assertCount(3, $checker->getCollections());
      
      $checker = clone $baseChecker;
      $checker->apply((new ClassPattern())->outputFull()->withModifier('final')->withModifier('abstract'));
      $this->assertCount(0, $checker->getCollections());

      $checker = clone $baseChecker;
      $checker->apply((new ClassPattern())->outputFull()->withAnyModifier());
      $this->assertCount(5, $checker->getCollections());

      
    }


    public function testWithoutModifier() {
      $baseChecker = new PatternMatcher(Collection::createFromString('<?php
      /**
       * description
       */
      abstract class A{public $a=1;} 
      
      class B{}
      /**
       */final class C{}
       
      final class D {}
      final class E {
      }
      '
      ));

      $checker = clone $baseChecker;
      $checker->apply((new ClassPattern())->outputFull()->withoutModifier('abstract'));
      $this->assertCount(4, $checker->getCollections());
      

      $checker = clone $baseChecker;
      $checker->apply((new ClassPattern())->outputFull()->withoutModifier('final'));
      $this->assertCount(2, $checker->getCollections());

      $checker = clone $baseChecker;
      $checker->apply((new ClassPattern())->outputFull()->withoutModifier('final')->withModifier('abstract'));
      $this->assertCount(1, $checker->getCollections());


      
    }


  }
