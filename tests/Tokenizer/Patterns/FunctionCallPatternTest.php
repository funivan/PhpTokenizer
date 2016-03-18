<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer\Patterns;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Pattern\Pattern;
  use Funivan\PhpTokenizer\Pattern\Patterns\FunctionCallPattern;
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

      $tokensChecker = new Pattern(Collection::createFromString($code));
      $tokensChecker->apply((new FunctionCallPattern()));
      $collections = $tokensChecker->getCollections();
      $this->assertCount(2, $collections);

      $this->assertEquals('trigger_error("Deprecated", E_USER_DEPRECATED)', (string) $collections[0]);
      $this->assertEquals('strlen(123)', (string) $collections[1]);
    }

  }
