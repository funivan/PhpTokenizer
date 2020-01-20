<?php

declare(strict_types=1);

namespace Test\Funivan\PhpTokenizer\Tokenizer\Patterns;

use Exception;
use Funivan\PhpTokenizer\Collection;
use Funivan\PhpTokenizer\Pattern\PatternMatcher;
use Funivan\PhpTokenizer\Pattern\Patterns\ParametersPattern;
use Funivan\PhpTokenizer\Query\Query;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 *
 */
class ParametersPatternTest extends TestCase
{


    public function testSimpleParameters()
    {


        $code = '<?php

      function test(\Adm\Users\Model $df = []  ){
        function($row, $col){
        }
        array($df);
        strtolower($df);
      }

      ($aa);
      ';
        $tokensChecker = new PatternMatcher(Collection::createFromString($code));
        $tokensChecker->apply((new ParametersPattern()));
        $collections = $tokensChecker->getCollections();

        static::assertCount(5, $collections);
        static::assertEquals('\Adm\Users\Model $df = []  ', (string)$collections[0]);
        static::assertEquals('$row, $col', (string)$collections[1]);
        static::assertEquals('$df', (string)$collections[2]);
    }


    public function testWithArgument()
    {


        $code = '<?php

      function test(\Adm\Users\Model $df = []){
      }

      function custom($data, $row){
      }
      function other($data, $row, $new ){
      }

      ';
        $tokensChecker = new PatternMatcher(Collection::createFromString($code));
        $tokensChecker->apply((new ParametersPattern()));
        $collections = $tokensChecker->getCollections();
        static::assertCount(3, $collections);


        $tokensChecker = new PatternMatcher(Collection::createFromString($code));
        $tokensChecker->apply((new ParametersPattern())->withArgument(1));
        $collections = $tokensChecker->getCollections();
        static::assertCount(3, $collections);

        $tokensChecker = new PatternMatcher(Collection::createFromString($code));
        $tokensChecker->apply((new ParametersPattern())->withArgument(2));
        $collections = $tokensChecker->getCollections();
        static::assertCount(2, $collections);
        static::assertEquals('$data, $row', (string)$collections[0]);
        static::assertEquals('$data, $row, $new ', (string)$collections[1]);

        $tokensChecker = new PatternMatcher(Collection::createFromString($code));
        $tokensChecker->apply((new ParametersPattern())->withArgument(3));
        $collections = $tokensChecker->getCollections();
        static::assertCount(1, $collections);
        static::assertEquals('$data, $row, $new ', (string)$collections[0]);
    }


    public function df()
    {

    }


    public function testWithArgumentCheck()
    {


        $code = '<?php

      function test(\Adm\Users\Model $df = []){
      }

      function custom($data, $row){
      }

      ';
        $tokensChecker = new PatternMatcher(Collection::createFromString($code));
        $tokensChecker->apply((new ParametersPattern()));
        $collections = $tokensChecker->getCollections();
        static::assertCount(2, $collections);


        $tokensChecker = new PatternMatcher(Collection::createFromString($code));
        $tokensChecker->apply((new ParametersPattern())->withArgument(1, function (Collection $collection) {
            $assign = $collection->find((new Query())->valueIs('='));
            return ($assign->count() > 0);
        }));
        $collections = $tokensChecker->getCollections();
        static::assertCount(1, $collections);
        static::assertEquals('\Adm\Users\Model $df = []', (string)$collections[0]);
    }


    public function testWithArgumentAndArraysAsValues()
    {


        $code = '<?php

      function test($items = [45,array(1,4)], $data = [1,4]){
      }

      function custom($data = array (1,3,3,4,), $row, Adb $test){
      }
      function myFunction($data=array(4), $row, Adb $test, $df){
      }

      ';
        $tokensChecker = new PatternMatcher(Collection::createFromString($code));
        $tokensChecker->apply((new ParametersPattern()));
        $collections = $tokensChecker->getCollections();
        static::assertCount(3, $collections);


        $tokensChecker = new PatternMatcher(Collection::createFromString($code));
        $tokensChecker->apply(
            (new ParametersPattern())
                ->withArgument(2)
                ->withoutArgument(3)
        );

        $collections = $tokensChecker->getCollections();
        static::assertCount(1, $collections);

        $tokensChecker = new PatternMatcher(Collection::createFromString($code));
        $tokensChecker->apply((new ParametersPattern())->withArgument(3));
        $collections = $tokensChecker->getCollections();
        static::assertCount(2, $collections);
    }


    /**
     * @return array
     */
    public function getOutputRawParametersDataProvider()
    {
        return [];
    }


    /**
     * @return array
     */
    public function getOutputParametersDataProvider()
    {
        return [
            [
                'code' => 'function test($items = [45]   , $data = [1,4]) {}',
                'index' => 1,
                'prepared' => '$items = [45]',
                'raw' => '$items = [45]   ',
            ],
            [
                'code' => 'function test(  $items = [45]   , $data = [1,4]) {}',
                'index' => 1,
                'prepared' => '$items = [45]',
                'raw' => '  $items = [45]   ',
            ],
            [
                'code' => 'function test($items = [45], $data = [1,4]                 ) {}',
                'index' => 2,
                'prepared' => '$data = [1,4]',
                'raw' => ' $data = [1,4]                 ',

            ],
            [
                'code' => 'custom_call(function($data, $error = []){ }, 54) {}',
                'index' => 1,
                'prepared' => 'function($data, $error = []){ }',
                'raw' => 'function($data, $error = []){ }',
            ],
        ];
    }


    /**
     * @dataProvider getOutputParametersDataProvider
     * @param string $code
     * @param int $index
     * @param string $expectPrepared
     * @param string $expectRaw
     */
    public function testOutputParameters($code, $index, $expectPrepared, $expectRaw)
    {

        $tokensChecker = self::createPatternMatch($code);
        $tokensChecker->apply((new ParametersPattern())->outputArgument($index));

        $collections = $tokensChecker->getCollections();
        static::assertCount(1, $collections);
        static::assertEquals($expectPrepared, (string)$collections[0]);


        $tokensChecker = self::createPatternMatch($code);
        $tokensChecker->apply((new ParametersPattern())->outputArgument($index, false));

        $collections = $tokensChecker->getCollections();
        static::assertCount(1, $collections);
        static::assertEquals($expectRaw, (string)$collections[0]);

    }


    public function testInvalidCheckFunction()
    {
        $tokensChecker = self::createPatternMatch('function custom($data, $row){ }');
        $pattern = (new ParametersPattern())->withArgument(1, function () {
            return new stdClass();
        });


        static::assertInstanceOf(ParametersPattern::class, $pattern);
        $this->expectException(Exception::class);
        $tokensChecker->apply($pattern);
    }


    public function testGetFirstArgument()
    {

        $pattern = new ParametersPattern();
        $pattern->withArgument(1);

        $result = self::createPatternMatch('
        function getCountry($userCache){ }
        function getName($id, $repository){ }
      ')->apply($pattern)->getCollections();

        self::assertCount(2, $result);
        self::assertEquals('$userCache', $result[0]->assemble());
    }


    /**
     * @param string $code
     * @return PatternMatcher
     */
    private static function createPatternMatch($code)
    {
        $collection = Collection::createFromString('<?php 
    ' . $code . '
  ');
        return new PatternMatcher($collection);
    }


    public function testGetWithSecondArgument()
    {
        $pattern = new ParametersPattern();
        $pattern->withArgument(2);
        $pattern->outputArgument(2);

        $result = self::createPatternMatch('
        function getCountry($userCache){ }
        function getName($id, $repository){ }
      ')->apply($pattern)->getCollections();

        self::assertCount(1, $result);
        self::assertEquals('$repository', $result[0]->assemble());
    }


    /**
     * @return array
     */
    public function getFetchSpecificArgumentDataProvider()
    {
        return [
            [
                'function getName($a , $b)',
                1,
                '$a',
            ],
            [
                'function getName($a , $b)',
                3,
                null,
            ],
            [
                'function getName($a , $b)',
                2,
                '$b',
            ],
            [
                'function getName($a , $b = [1,2,3])',
                2,
                '$b = [1,2,3]',
            ],
        ];
    }


    /**
     * @dataProvider getFetchSpecificArgumentDataProvider
     * @param $code
     * @param $argumentIndex
     * @param $output
     */
    public function testFetchSpecificArgument($code, $argumentIndex, $output)
    {
        $pattern = new ParametersPattern();
        $pattern->withArgument($argumentIndex);
        $pattern->outputArgument($argumentIndex);

        $result = self::createPatternMatch($code)
            ->apply($pattern)->getCollections();

        if ($output === null) {
            self::assertCount(0, $result);
        } else {
            self::assertCount(1, $result);
            self::assertEquals($output, $result[0]->assemble());
        }

    }

}
