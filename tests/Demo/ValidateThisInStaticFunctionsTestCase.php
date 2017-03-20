<?php

  declare(strict_types=1);

  namespace Test\Funivan\PhpTokenizer\Demo;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Pattern\PatternMatcher;
  use Funivan\PhpTokenizer\Query\Query;
  use Funivan\PhpTokenizer\QuerySequence\QuerySequence;
  use Funivan\PhpTokenizer\Strategy\Possible;

  class ValidateThisInStaticFunctionsTestCase extends \PHPUnit_Framework_TestCase {

    /**
     * @return array
     */
    public function getDemoCode() {

      return [
        [
          'public static function test(){echo $this;}',
          true,
        ],
        [
          'static private function test(){return $this}',
          true,
        ],
        [
          'public function test(){echo $this;}',
          false,
        ],
        [
          'static private function test(){}',
          false,
        ],
      ];
    }


    /**
     * @dataProvider getDemoCode
     * @param string $code
     * @param boolean $expectThis
     */
    public function testExtract($code, $expectThis) {
      $collection = Collection::createFromString("<?php " . $code);
      $containThis = false;
      (new PatternMatcher($collection))->apply(function (QuerySequence $q) use (&$containThis) {
        $q->setSkipWhitespaces(true);
        $q->strict('static');
        $q->process(Possible::create()->valueIs(['public', 'protected', 'private']));
        $q->strict(T_FUNCTION);
        $q->strict(T_STRING);
        $q->section('(', ')');
        $functionBody = $q->section('{', '}');

        $thisVariablesNum = $functionBody->find((new Query())->valueIs('$this'))->count();
        if ($thisVariablesNum > 0) {
          $containThis = true;
        }
      });


      self::assertEquals($expectThis, $containThis);
    }

  }
