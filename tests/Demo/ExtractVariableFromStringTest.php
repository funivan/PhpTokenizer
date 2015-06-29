<?php

  namespace Test\Funivan\PhpTokenizer\Demo;

  use Funivan\PhpTokenizer\Pattern\Pattern;
  use Funivan\PhpTokenizer\QuerySequence\QuerySequence;

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
        //array(
        //  'echo "$user->getName 123 ";',
        //  'echo "".$user->getName." 123 ";'
        //),
        //array(
        //  'echo "$user 123 ";',
        //  'echo "".$user." 123 ";'
        //),
        //array(
        //  'echo "$user";',
        //  'echo "".$user."";'
        //),
        array(
          'echo "$user or $user 123 ";',
          'echo "".$user." or ".$user." 123 ";',
        ),
        //array(
        //  'echo "custom $user 123 ";',
        //  'echo "custom ".$user." 123 ";'
        //),

        //array(
        //  'echo "$data";',
        //  'echo "".$data."";'
        //),
        //
        //array(
        //  'echo "$start  ";',
        //  'echo "".$start."  ";'
        //),
        //array(
        //  'echo "custom $end";',
        //  'echo "custom ".$end."";',
        //),
        //array(
        //  'echo "$data custom $end";',
        //  'echo "".$data." custom ".$end."";',
        //),

      );
    }

    /**
     * @dataProvider getDemoCode
     * @param string $code
     * @param string $expectCode
     */
    public function testExtract($code, $expectCode) {
      $collection = \Funivan\PhpTokenizer\Collection::initFromString("<?php " . $code);

      $checker = new Pattern($collection);
      $checker->apply(function (QuerySequence $q) {

        $q->strict('"');
        $q->possible(T_ENCAPSED_AND_WHITESPACE);
        $variable = $q->strict(T_VARIABLE);
        $arrow = $q->possible('->');
        if ($arrow->isValid()) {
          $property = $q->strict(T_STRING);
        }


        if ($q->isValid()) {
          $variable->prependToValue('".');
          if (!empty($property) and $property->isValid()) {
            $property->appendToValue('."');
          } else {
            $variable->appendToValue('."');
          }

          $q->getCollection()->refresh();
        }
        
      });

      $collection[0]->remove();
      $this->assertEquals($expectCode, (string) $collection);
    }
  }
