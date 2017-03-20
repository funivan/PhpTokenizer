<?php

  declare(strict_types=1);

  namespace Test\Funivan\PhpTokenizer\Demo;

  use Funivan\PhpTokenizer\QuerySequence\QuerySequence;
  use Funivan\PhpTokenizer\Strategy\Possible;
  use Funivan\PhpTokenizer\Strategy\Strict;

  class RemoveEmptyConcatenatedStringsTest extends \PHPUnit_Framework_TestCase {


    /**
     * @return array
     */
    public function getDemoCode() : array {
      return [
        [
          'echo $user."";',
          'echo $user;',
        ],
        [
          'echo $user ."";',
          'echo $user;',
        ],

        [
          'echo $user . "" ;',
          'echo $user;',
        ],

        [
          'echo $user.""          ;',
          'echo $user;',
        ],
        [
          'echo $user.""          ."user";',
          'echo $user."user";',
        ],
        [
          'echo $user.\'\'.$user;',
          'echo $user.$user;',
        ],
        [
          'echo "".$user;',
          'echo $user;',
        ],
        [
          'echo "".$user.""."".$name;',
          'echo $user.$name;',
        ],

        [
          'echo "111".$user.""."".$name;',
          'echo "111".$user.$name;',
        ],

        [
          'echo ""."".$name;',
          'echo $name;',
        ],
      ];
    }


    /**
     * @dataProvider getDemoCode
     * @param string $code
     * @param string $expectCode
     */
    public function testRemoveEmptyString($code, $expectCode) {
      $collection = \Funivan\PhpTokenizer\Collection::createFromString('<?php ' . $code);


      foreach ($collection as $index => $token) {
        $p = new QuerySequence($collection, $index);

        # remove empty string and dot
        $sequence = $p->sequence([
          Strict::create()->valueIs(["''", '""']),
          Possible::create()->typeIs(T_WHITESPACE),
          Strict::create()->valueIs('.'),
          Possible::create()->typeIs(T_WHITESPACE),
        ]);

        if ($p->isValid()) {
          $sequence->remove();
        }

        # remove empty dot and empty string

        $p->setValid(true)->setPosition($index);

        $sequence = $p->sequence([
          Possible::create()->typeIs(T_WHITESPACE),
          Strict::create()->valueIs('.'),
          Possible::create()->typeIs(T_WHITESPACE),
          Strict::create()->valueIs(["''", '""']),
          Possible::create()->typeIs(T_WHITESPACE),
        ]);

        if ($p->isValid()) {
          $sequence->remove();
        }

      }

      $collection[0]->remove();
      $this->assertEquals($expectCode, (string) $collection);
    }

  }
