<?

  namespace Test\Funivan\PhpTokenizer\Demo;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\QuerySequence\QuerySequence;
  use Funivan\PhpTokenizer\Strategy\Possible;

  class ValidateThisInStaticFunctions extends \PHPUnit_Framework_TestCase {

    /**
     * @return array
     */
    public function getDemoCode() {

      return array(
        array(
          'public static function test(){echo $this;}',
          true,
        ),
        array(
          'public function test(){echo $this;}',
          false,
        ),
        array(
          'static private function test(){}',
          false,
        ),
        array(
          'static private function test(){return $this}',
          true,
        ),
      );
    }


    /**
     * @dataProvider getDemoCode
     * @param string $code
     * @param boolean $expectThis
     */
    public function testExtract($code, $expectThis) {
      $collection = Collection::initFromString("<?php " . $code);
      $stream = new QuerySequence($collection, true);

      $containThis = false;
      # remove empty string and dot    
      while ($p = $stream->getProcessor()) {


        $p->strict('static');
        $p->process(Possible::create()->valueIs(['public', 'protected', 'private']));
        $p->strict(T_FUNCTION);
        $functionBody = $p->section('{', '}');
        if (!$p->isValid()) {
          continue;
        }


        foreach ($functionBody as $token) {
          if ($token->getValue() == '$this') {
            $containThis = true;
            break;
          }
        }

      }

      $this->assertEquals($expectThis, $containThis);
    }

  }