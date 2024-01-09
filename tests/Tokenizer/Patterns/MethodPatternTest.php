<?php

declare(strict_types=1);

namespace Test\Funivan\PhpTokenizer\Tokenizer\Patterns;

use Funivan\PhpTokenizer\Collection;
use Funivan\PhpTokenizer\Pattern\PatternMatcher;
use Funivan\PhpTokenizer\Pattern\Patterns\MethodPattern;
use Funivan\PhpTokenizer\Pattern\Patterns\ParametersPattern;
use Funivan\PhpTokenizer\Query\Query;
use Funivan\PhpTokenizer\Strategy\Strict;
use Funivan\PhpTokenizer\Token;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;

class MethodPatternTest extends TestCase
{
    public function testMatchMethodWithoutName(): void
    {
        $code = '<?php
      
      function test(){
      
      }
      
      ';

        $tokensChecker = new PatternMatcher(Collection::createFromString($code));
        $tokensChecker->apply(new MethodPattern());
        static::assertCount(1, $tokensChecker->getCollections());

        $pattern = new MethodPattern();
        $pattern->withName('test');
        $tokensChecker = new PatternMatcher(Collection::createFromString($code));
        static::assertCount(1, $tokensChecker->apply($pattern)->getCollections());

        $pattern = new MethodPattern();
        $pattern->withName('TEST');
        $tokensChecker = new PatternMatcher(Collection::createFromString($code));
        static::assertCount(0, $tokensChecker->apply($pattern)->getCollections());

        $pattern = new MethodPattern();
        $pattern->withName(Strict::create()->valueLike('!te.+!'));
        $tokensChecker = new PatternMatcher(Collection::createFromString($code));
        static::assertCount(1, $tokensChecker->apply($pattern)->getCollections());
    }

    public function testMatchWithEmptyStartFrom(): void
    {
        $code = '<?php function test(){

      }

      ';

        $tokensChecker = new PatternMatcher(Collection::createFromString($code));
        $tokensChecker->apply(new MethodPattern());
        static::assertCount(1, $tokensChecker->getCollections());
    }

    public function testMatchWithParameters(): void
    {
        $code = '<?php
      function showUser($user){ }

      function test($a, $b){ }

      ';

        $tokensChecker = new PatternMatcher(Collection::createFromString($code));
        $tokensChecker->apply((new MethodPattern())->withParameters(new ParametersPattern()));
        static::assertCount(2, $tokensChecker->getCollections());

        $tokensChecker = new PatternMatcher(Collection::createFromString($code));
        $tokensChecker->apply((new MethodPattern())->withParameters((new ParametersPattern())->withArgument(2)));
        static::assertCount(1, $tokensChecker->getCollections());
    }

    public function testMatchWithMultipleKeywords(): void
    {
        $code = '<?php

      public static final function test(){

      }

      public function test(){

      }

      function test(){

      }

      ';

        $tokensChecker = new PatternMatcher(Collection::createFromString($code));
        $tokensChecker->apply(new MethodPattern());
        static::assertCount(3, $tokensChecker->getCollections());

        $tokensChecker = new PatternMatcher(Collection::createFromString($code));
        $tokensChecker->apply((new MethodPattern())->withModifier('static'));
        static::assertCount(1, $tokensChecker->getCollections());

        $tokensChecker = new PatternMatcher(Collection::createFromString($code));
        $tokensChecker->apply((new MethodPattern())->withModifier('public'));
        static::assertCount(2, $tokensChecker->getCollections());

        $tokensChecker = new PatternMatcher(Collection::createFromString($code));
        $tokensChecker->apply((new MethodPattern())->withoutModifier('public'));
        static::assertCount(1, $tokensChecker->getCollections());
    }

    public function testDocCommentMatch(): void
    {
        $code = '<?php
     /**
      * hello
      */
      public static final function test0(){
         echo "test0";
      }


      /**
       * hello test
       */
      public function test1(){
         echo "test1";
      }

      public function test2(){
        echo "test2";
      }

      ';

        $tokensChecker = new PatternMatcher(Collection::createFromString($code));
        $tokensChecker->apply(new MethodPattern());
        static::assertCount(3, $tokensChecker->getCollections());

        $tokensChecker = new PatternMatcher(Collection::createFromString($code));
        $tokensChecker->apply((new MethodPattern())->withDocComment());
        static::assertCount(2, $tokensChecker->getCollections());

        $tokensChecker = new PatternMatcher(Collection::createFromString($code));
        $tokensChecker->apply((new MethodPattern())->withoutDocComment());
        static::assertCount(1, $tokensChecker->getCollections());

        $tokensChecker = new PatternMatcher(Collection::createFromString($code));
        $tokensChecker->apply((new MethodPattern())->withDocComment(fn (Token $token) => str_contains($token->getValue() ?? '', 'hello')));

        static::assertCount(2, $tokensChecker->getCollections());

        $tokensChecker = new PatternMatcher(Collection::createFromString($code));
        $tokensChecker->apply((new MethodPattern())->withDocComment(fn (Token $token) => str_contains($token->getValue() ?? '', 'test')));

        static::assertCount(1, $tokensChecker->getCollections());
    }

    public function testBodyFiler(): void
    {
        $code = '<?php

      public static final function test0(){
         echo "test0";
      }

      public function test1(){
         echo "test11";
      }

      public function test2(){
        echo "test22";
      }

      ';

        $tokensChecker = new PatternMatcher(Collection::createFromString($code));
        $tokensChecker->apply(new MethodPattern());
        static::assertCount(3, $tokensChecker->getCollections());

        $tokensChecker = new PatternMatcher(Collection::createFromString($code));
        $tokensChecker->apply((new MethodPattern())->withBody(fn (Collection $collection) => $collection->find((new Query())->typeIs(T_ECHO))->count() > 0));

        $tokensChecker = new PatternMatcher(Collection::createFromString($code));
        $tokensChecker->apply((new MethodPattern())->withBody(fn (Collection $collection) => $collection->find((new Query())->valueLike('!test22!'))->count() === 1));

        static::assertCount(1, $tokensChecker->getCollections());
    }

    public function testOutput(): void
    {
        $code = '<?php
             /**
              * comment
              */
      public static final function test0(){
         echo "test0";
      }

      ';

        $tokensChecker = new PatternMatcher(Collection::createFromString($code));
        $tokensChecker->apply(new MethodPattern());
        $collections = $tokensChecker->getCollections();
        static::assertCount(1, $collections);
        static::assertStringContainsString('echo "test0";', (string) $collections[0]);

        $tokensChecker = new PatternMatcher(Collection::createFromString($code));
        $tokensChecker->apply((new MethodPattern())->outputBody());
        $collections = $tokensChecker->getCollections();
        static::assertCount(1, $collections);
        static::assertStringContainsString('echo "test0";', (string) $collections[0]);

        $tokensChecker = new PatternMatcher(Collection::createFromString($code));
        $tokensChecker->apply((new MethodPattern())->outputFull());

        $collections = $tokensChecker->getCollections();
        static::assertCount(1, $collections);
        static::assertStringContainsString('* comment', (string) $collections[0]);
        static::assertStringContainsString('public static final function test0(){', (string) $collections[0]);
        static::assertStringContainsString('echo "test0";', (string) $collections[0]);

        $tokensChecker = new PatternMatcher(Collection::createFromString($code));
        $tokensChecker->apply((new MethodPattern())->outputDocComment());

        $collections = $tokensChecker->getCollections();
        static::assertCount(1, $collections);
        static::assertStringContainsString('* comment', (string) $collections[0]);

        $comment = Collection::createFromString($code)->find((new Query())->typeIs(T_DOC_COMMENT))->getFirst();
        static::assertEquals($comment->getValue(), (string) $collections[0]);
    }

    public function testInvalidNameQuery(): void
    {
        $pattern = new MethodPattern();
        $this->expectException(InvalidArgumentException::class);
        $pattern->withName(new stdClass());
    }
}
