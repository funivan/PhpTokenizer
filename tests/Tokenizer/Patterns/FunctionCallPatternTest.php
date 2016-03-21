<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer\Patterns;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Pattern\PatternMatcher;
  use Funivan\PhpTokenizer\Pattern\Patterns\ArgumentsPattern;
  use Funivan\PhpTokenizer\Pattern\Patterns\FunctionCallPattern;
  use Funivan\PhpTokenizer\Query\Query;
  use Test\Funivan\PhpTokenizer\MainTestCase;

  /**
   *
   */
  class FunctionCallPatternTest extends MainTestCase {

    public function testDetectFunctionCall() {


      $code = '<?php

      echo @trigger_error("Deprecated", E_USER_DEPRECATED);
      echo strlen(123);

      function test2(){
        echo "test22";
      }

      function(){
        echo "ololo";
      }

      Data::call(123);
      Other:: call(123);

      ';

      $tokensChecker = new PatternMatcher(Collection::createFromString($code));
      $tokensChecker->apply((new FunctionCallPattern()));
      $collections = $tokensChecker->getCollections();
      $this->assertCount(2, $collections);

      $this->assertEquals('trigger_error("Deprecated", E_USER_DEPRECATED)', (string) $collections[0]);
      $this->assertEquals('strlen(123)', (string) $collections[1]);
    }


    public function testWithName() {


      $code = '<?php

      echo @trigger_error("Deprecated", E_USER_DEPRECATED);
      echo strlen(123);

      ';

      $tokensChecker = new PatternMatcher(Collection::createFromString($code));
      $tokensChecker->apply((new FunctionCallPattern())->withName(new Query()));
      $collections = $tokensChecker->getCollections();
      $this->assertCount(2, $collections);

      $tokensChecker->apply((new FunctionCallPattern())->withName((new Query())->valueLike('!^str.+$!')));
      $collections = $tokensChecker->getCollections();
      $this->assertCount(1, $collections);

    }


    /**
     * @faq How to find function with specific arguments num
     */
    public function testWithParameters() {


      $code = '<?php

      echo @trigger_error("Deprecated", E_USER_DEPRECATED);
      echo strlen(123);

      ';

      # configure out pattern
      $functionPattern = (new FunctionCallPattern())
        ->withParameters(
          (new ArgumentsPattern())
            ->withArgument(2)
        );

      #
      $tokens = Collection::createFromString($code);
      $tokensChecker = new PatternMatcher($tokens);
      $tokensChecker->apply($functionPattern);

      # get result
      $collections = $tokensChecker->getCollections();

      $this->assertCount(1, $collections);

      $this->assertEquals('trigger_error("Deprecated", E_USER_DEPRECATED)', $collections[0]);
    }

  }
