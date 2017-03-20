<?php

  declare(strict_types=1);

  namespace Test\Funivan\PhpTokenizer\Demo;

  use Funivan\PhpTokenizer\Pattern\PatternMatcher;
  use Funivan\PhpTokenizer\QuerySequence\QuerySequence;
  use Funivan\PhpTokenizer\Token;


  class ExtractVariableFromStringTest extends \PHPUnit_Framework_TestCase {


    public function getDemoCode() : array {
      return [
        [
          'echo "$user->getName 123 ";',
          'echo "".$user->getName." 123 ";',
        ],
        [
          'echo "$user 123 ";',
          'echo "".$user." 123 ";',
        ],
        [
          'echo "$user";',
          'echo "".$user."";',
        ],
        [
          'echo "$user or $user 123 ";',
          'echo "".$user." or ".$user." 123 ";',
        ],
        [
          'echo "custom $user 123 ";',
          'echo "custom ".$user." 123 ";',
        ],

        [
          'echo "$data";',
          'echo "".$data."";',
        ],

        [
          'echo "$start  ";',
          'echo "".$start."  ";',
        ],
        [
          'echo "custom $end";',
          'echo "custom ".$end."";',
        ],
        [
          'echo "$data custom $end";',
          'echo "".$data." custom ".$end."";',
        ],

      ];
    }


    /**
     * @dataProvider getDemoCode
     * @param string $code
     * @param string $expectCode
     */
    public function testExtract($code, $expectCode) {
      $collection = \Funivan\PhpTokenizer\Collection::createFromString('<?php ' . $code);

      $checker = new PatternMatcher($collection);
      $checker->apply(function (QuerySequence $q) {

        $q->strict('"');
        $q->possible(T_ENCAPSED_AND_WHITESPACE);
        $variable = $q->strict(T_VARIABLE);
        $arrow = $q->possible('->');
        $property = new Token();
        if ($arrow->isValid()) {
          $property = $q->strict(T_STRING);
        }


        if ($q->isValid()) {
          $variable->prependToValue('".');
          if ($property->isValid()) {
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
