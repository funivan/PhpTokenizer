<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer\Patterns;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Pattern\Pattern;
  use Funivan\PhpTokenizer\Pattern\Patterns\ParametersPattern;
  use Funivan\PhpTokenizer\Query\Query;
  use Test\Funivan\PhpTokenizer\MainTestCase;

  /**
   *
   */
  class ParametersPatternTest extends MainTestCase {


    public function testSimpleParameters() {


      $code = '<?php

      function test(\Adm\Users\Model $df = []  ){
        function($row, $col){
        }
        array($df);
        strtolower($df);
      }

      ($aa);
      ';
      $tokensChecker = new Pattern(Collection::createFromString($code));
      $tokensChecker->apply((new ParametersPattern()));
      $collections = $tokensChecker->getCollections();

      $this->assertCount(2, $collections);
      $this->assertEquals('\Adm\Users\Model $df = []  ', (string) $collections[0]);
      $this->assertEquals('$row, $col', (string) $collections[1]);
    }


    public function testWithArgument() {


      $code = '<?php

      function test(\Adm\Users\Model $df = []){
      }

      function custom($data, $row){
      }
      function other($data, $row, $new ){
      }

      ';
      $tokensChecker = new Pattern(Collection::createFromString($code));
      $tokensChecker->apply((new ParametersPattern()));
      $collections = $tokensChecker->getCollections();
      $this->assertCount(3, $collections);


      $tokensChecker = new Pattern(Collection::createFromString($code));
      $tokensChecker->apply((new ParametersPattern())->withArgument(1));
      $collections = $tokensChecker->getCollections();
      $this->assertCount(3, $collections);

      $tokensChecker = new Pattern(Collection::createFromString($code));
      $tokensChecker->apply((new ParametersPattern())->withArgument(2));
      $collections = $tokensChecker->getCollections();
      $this->assertCount(2, $collections);
      $this->assertEquals('$data, $row', (string) $collections[0]);
      $this->assertEquals('$data, $row, $new ', (string) $collections[1]);

      $tokensChecker = new Pattern(Collection::createFromString($code));
      $tokensChecker->apply((new ParametersPattern())->withArgument(3));
      $collections = $tokensChecker->getCollections();
      $this->assertCount(1, $collections);
      $this->assertEquals('$data, $row, $new ', (string) $collections[0]);
    }


    public function df() {

    }


    public function testWithArgumentCheck() {


      $code = '<?php

      function test(\Adm\Users\Model $df = []){
      }

      function custom($data, $row){
      }

      ';
      $tokensChecker = new Pattern(Collection::createFromString($code));
      $tokensChecker->apply((new ParametersPattern()));
      $collections = $tokensChecker->getCollections();
      $this->assertCount(2, $collections);


      $tokensChecker = new Pattern(Collection::createFromString($code));
      $tokensChecker->apply((new ParametersPattern())->withArgument(1, function (Collection $collection) {
        $assign = $collection->find((new Query())->valueIs('='));
        return ($assign->count() > 0);
      }));
      $collections = $tokensChecker->getCollections();
      $this->assertCount(1, $collections);
      $this->assertEquals('\Adm\Users\Model $df = []', (string) $collections[0]);
    }


    public function testWithArgumentAndArraysAsValues() {


      $code = '<?php

      function test($items = [45,array(1,4)], $data = [1,4]){
      }

      function custom($data = array (1,3,3,4,), $row, Adb $test){
      }
      function myFunction($data=array(4), $row, Adb $test, $df){
      }

      ';
      $tokensChecker = new Pattern(Collection::createFromString($code));
      $tokensChecker->apply((new ParametersPattern()));
      $collections = $tokensChecker->getCollections();
      $this->assertCount(3, $collections);


      $tokensChecker = new Pattern(Collection::createFromString($code));
      $tokensChecker->apply(
        (new ParametersPattern())
          ->withArgument(2)
          ->withoutArgument(3)
      );

      $collections = $tokensChecker->getCollections();
      $this->assertCount(1, $collections);

      $tokensChecker = new Pattern(Collection::createFromString($code));
      $tokensChecker->apply((new ParametersPattern())->withArgument(3));
      $collections = $tokensChecker->getCollections();
      $this->assertCount(2, $collections);
    }


    public function getOutputArgumentsDataProvider() {
      return [
        [
          'function test($items = [45], $data = [1,4]) {}',
          1,
          '$items = [45]',
        ],

        [
          'function test($items = [45], $data = [1,4] ) {}',
          2,
          ' $data = [1,4] ',
        ],

      ];
    }


    /**
     * @dataProvider getOutputArgumentsDataProvider
     * @param string $code
     * @param int $index
     * @param string $expect
     */
    public function testOutputArguments($code, $index, $expect) {

      $tokensChecker = new Pattern(Collection::createFromString('<?php  ' . $code));
      $tokensChecker->apply((new ParametersPattern())->outputArgument($index));
      $collections = $tokensChecker->getCollections();
      $this->assertCount(1, $collections);

      $this->assertEquals($expect, (string) $collections[0]);
    }


    /**
     * @expectedException \Exception
     */
    public function testInvalidCheckFunction() {
      $code = '<?php
      function custom($data, $row){
      }
      ';

      $tokensChecker = new Pattern(Collection::createFromString($code));
      $pattern = (new ParametersPattern())->withArgument(1, function () {
        return new \stdClass();
      });


      $this->assertInstanceOf(ParametersPattern::class, $pattern);

      $tokensChecker->apply($pattern);
    }


  }
