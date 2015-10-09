<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer\Patterns;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Pattern\Pattern;
  use Funivan\PhpTokenizer\Pattern\Patterns\MethodPattern;
  use Funivan\PhpTokenizer\Strategy\Strict;
  use Test\Funivan\PhpTokenizer\MainTestCase;

  /**
   *
   * @package Test\Funivan\PhpTokenizer\Tokenizer\Patterns
   */
  class MethodPatternTest extends MainTestCase {


    public function testMatchMethodWithoutName() {

      $code = '<?php
      
      function test(){
      
      }
      
      ';

      $tokensChecker = new Pattern(Collection::createFromString($code));
      $tokensChecker->apply((new MethodPattern()));
      $this->assertCount(1, $tokensChecker->getCollections());


      $pattern = new MethodPattern();
      $pattern->withName('test');
      $tokensChecker = new Pattern(Collection::createFromString($code));
      $this->assertCount(1, $tokensChecker->apply($pattern)->getCollections());


      $pattern = new MethodPattern();
      $pattern->withName('TEST');
      $tokensChecker = new Pattern(Collection::createFromString($code));
      $this->assertCount(0, $tokensChecker->apply($pattern)->getCollections());


      $pattern = new MethodPattern();
      $pattern->withName(Strict::create()->valueLike('!te.+!'));
      $tokensChecker = new Pattern(Collection::createFromString($code));
      $this->assertCount(1, $tokensChecker->apply($pattern)->getCollections());

    }


    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidNameQuery() {
      $pattern = new MethodPattern();
      $pattern->withName(new \stdClass());
    }


  }
