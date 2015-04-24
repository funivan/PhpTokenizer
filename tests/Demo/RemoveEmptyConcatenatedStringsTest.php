<?php

  namespace Test\Funivan\PhpTokenizer\Demo;

  use Funivan\PhpTokenizer\Strategy\Possible;
  use Funivan\PhpTokenizer\Strategy\Strict;
  use Funivan\PhpTokenizer\StreamProcess\StreamProcess;

  class RemoveEmptyConcatenatedStringsTest extends \Test\Funivan\PhpTokenizer\MainTestCase {


    public function getDemoCode() {
      return array(
        array(
          'echo $user."";',
          'echo $user;'
        ),
        array(
          'echo $user ."";',
          'echo $user;'
        ),

        array(
          'echo $user . "" ;',
          'echo $user;'
        ),

        array(
          'echo $user.""          ;',
          'echo $user;'
        ),
        array(
          'echo $user.""          ."user";',
          'echo $user."user";'
        ),
        array(
          'echo $user.\'\'.$user;',
          'echo $user.$user;'
        ),
        array(
          'echo "".$user;',
          'echo $user;'
        ),
        array(
          'echo "".$user.""."".$name;',
          'echo $user.$name;'
        ),

        array(
          'echo "111".$user.""."".$name;',
          'echo "111".$user.$name;'
        ),

        array(
          'echo ""."".$name;',
          'echo $name;'
        ),
      );
    }

    /**
     * @dataProvider getDemoCode
     * @param string $code
     * @param string $expectCode
     */
    public function testRemoveString($code, $expectCode) {
      $collection = \Funivan\PhpTokenizer\Collection::initFromString("<?php " . $code);


      $stream = new StreamProcess($collection);

      while ($p = $stream->getProcessor()) {
        $startFrom = $p->getPosition();

        # remove empty string and dot    
        $sequence = $p->sequence(array(
          Strict::create()->valueIs(["''", '""']),
          Possible::create()->typeIs(T_WHITESPACE),
          Strict::create()->valueIs('.'),
          Possible::create()->typeIs(T_WHITESPACE),
        ));

        if ($p->isValid()) {
          $sequence->remove();
        }

        # remove empty dot and empty string
        $p->setValid(true)->moveTo($startFrom);
        $sequence = $p->sequence(array(
          Possible::create()->typeIs(T_WHITESPACE),
          Strict::create()->valueIs('.'),
          Possible::create()->typeIs(T_WHITESPACE),
          Strict::create()->valueIs(["''", '""']),
          Possible::create()->typeIs(T_WHITESPACE),
        ));

        if ($p->isValid()) {
          $sequence->remove();
        }

      }

      $collection[0]->remove();
      $this->assertEquals($expectCode, (string) $collection);
    }

  }
