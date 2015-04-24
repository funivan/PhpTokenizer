<?php

  namespace Test\Funivan\PhpTokenizer\Demo;

  use Funivan\PhpTokenizer\StreamProcess\StreamProcess;

  /**
   *
   * @package Test\Funivan\PhpTokenizer\Demo
   */
  class ExtractVariableFromStringTest extends \Test\Funivan\PhpTokenizer\MainTestCase {

    /**
     * @return array
     */
    public function getDemoCode() {
      return array(
        array(
          'echo "$user->getName 123 ";',
          'echo "".$user->getName." 123 ";'
        ),
        array(
          'echo "$user 123 ";',
          'echo "".$user." 123 ";'
        ),
        array(
          'echo "$user";',
          'echo "".$user."";'
        ),
        array(
          'echo "$user or $user 123 ";',
          'echo "".$user." or ".$user." 123 ";',
        ),
        array(
          'echo "custom $user 123 ";',
          'echo "custom ".$user." 123 ";'
        ),

        array(
          'echo "$data";',
          'echo "".$data."";'
        ),

        array(
          'echo "$start  ";',
          'echo "".$start."  ";'
        ),
        array(
          'echo "custom $end";',
          'echo "custom ".$end."";',
        ),
        array(
          'echo "$data custom $end";',
          'echo "".$data." custom ".$end."";',
        ),

      );
    }

    /**
     * @dataProvider getDemoCode
     * @param string $code
     * @param string $expectCode
     */
    public function testExtract($code, $expectCode) {
      $collection = \Funivan\PhpTokenizer\Collection::initFromString("<?php " . $code);

      $stream = new StreamProcess($collection);


      # extract variable from string  
      while ($p = $stream->getProcessor()) {
        $p->strict('"');
        $p->possible(T_ENCAPSED_AND_WHITESPACE);
        $variable = $p->strict(T_VARIABLE);
        $arrow = $p->possible('->');
        if ($arrow->isValid()) {
          $property = $p->strict(T_STRING);
        }


        if ($p->isValid()) {
          $variable->prependToValue('".');
          if (!empty($property) and $property->isValid()) {
            $property->appendToValue('."');
          } else {
            $variable->appendToValue('."');
          }
        }

      }


      $collection[0]->remove();
      $this->assertEquals($expectCode, (string) $collection);
    }
  }
