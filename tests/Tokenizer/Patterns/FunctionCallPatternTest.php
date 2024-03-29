<?php

declare(strict_types=1);

namespace Test\Funivan\PhpTokenizer\Tokenizer\Patterns;

use Funivan\PhpTokenizer\Collection;
use Funivan\PhpTokenizer\Pattern\PatternMatcher;
use Funivan\PhpTokenizer\Pattern\Patterns\FunctionCallPattern;
use Funivan\PhpTokenizer\Pattern\Patterns\ParametersPattern;
use Funivan\PhpTokenizer\Query\Query;
use PHPUnit\Framework\TestCase;

class FunctionCallPatternTest extends TestCase
{
    public function testDetectFunctionCall(): void
    {
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
        $tokensChecker->apply((new FunctionCallPattern())->outputFull());
        $collections = $tokensChecker->getCollections();
        static::assertCount(2, $collections);

        static::assertEquals('trigger_error("Deprecated", E_USER_DEPRECATED)', (string) $collections[0]);
        static::assertEquals('strlen(123)', (string) $collections[1]);
    }

    public function testWithName(): void
    {
        $code = '<?php

      echo @trigger_error("Deprecated", E_USER_DEPRECATED);
      echo strlen(123);

      ';

        $tokensChecker = new PatternMatcher(Collection::createFromString($code));
        $tokensChecker->apply((new FunctionCallPattern())->withName(new Query()));
        $collections = $tokensChecker->getCollections();
        static::assertCount(2, $collections);

        $tokensChecker->apply((new FunctionCallPattern())->withName((new Query())->valueLike('!^str.+$!')));
        $collections = $tokensChecker->getCollections();
        static::assertCount(1, $collections);
    }

    /**
     * @faq function :: How to find function and fetch only its arguments
     */
    public function testOutputArguments(): void
    {
        $code = '<?php

      echo @trigger_error("Deprecated", E_USER_DEPRECATED);
      echo strlen(123);
      ';

        $matcher = (new PatternMatcher(Collection::createFromString($code)))
            ->apply(
                (new FunctionCallPattern())->outputArguments() # configure our pattern
            );

        # get result
        $collections = $matcher->getCollections();

        static::assertCount(2, $collections);
        static::assertEquals('("Deprecated", E_USER_DEPRECATED)', (string) $collections[0]);
    }

    /**
     * @faq How to find function with specific arguments num
     */
    public function testWithParameters(): void
    {
        $code = '<?php

      echo @trigger_error("Deprecated", E_USER_DEPRECATED);
      echo strlen(123);

      ';

        # configure our pattern
        $functionPattern = (new FunctionCallPattern())
            ->withParameters(
                (new ParametersPattern())
                    ->withArgument(2)
            );

        #
        $tokens = Collection::createFromString($code);
        $tokensChecker = new PatternMatcher($tokens);
        $tokensChecker->apply($functionPattern);

        # get result
        $collections = $tokensChecker->getCollections();

        static::assertCount(1, $collections);

        static::assertEquals('trigger_error("Deprecated", E_USER_DEPRECATED)', (string) $collections[0]);
    }
}
