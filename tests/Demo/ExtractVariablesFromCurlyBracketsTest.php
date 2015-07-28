<?php

  namespace Test\Funivan\PhpTokenizer\Demo;

  use Funivan\PhpTokenizer\QuerySequence\QuerySequence;

  class ExtractVariablesFromCurlyBracketsTest extends \PHPUnit_Framework_TestCase {

    /**
     * @return array
     */
    public function getDemoCode() {

      return array(
        array(
          'echo "{$user->getName} 123 ";',
          'echo "".$user->getName." 123 ";'
        ),
        array(
          'echo "{$user->getName}";',
          'echo "".$user->getName."";'
        ),
        array(
          'echo "name: {$user->getName->upFirst()} <- ";',
          'echo "name: ".$user->getName->upFirst()." <- ";',
        ),
        array(
          'echo "$user name: {$user->getName->upFirst()} <- ";',
          'echo "$user name: ".$user->getName->upFirst()." <- ";',
        ),

      );
    }

    /**
     * @dataProvider getDemoCode
     * @param string $code
     * @param string $expectCode
     */
    public function testExtract($code, $expectCode) {
      $collection = \Funivan\PhpTokenizer\Collection::createFromString("<?php " . $code);


      # remove empty string and dot    
      foreach ($collection as $index => $token) {
        $p = new QuerySequence($collection, $index);
        $quote = $p->possible('"');
        if ($quote->isValid() == false) {
          $p->strict(T_ENCAPSED_AND_WHITESPACE);
        }


        $start = $p->strict("{");
        $p->strict(T_VARIABLE);
        $end = $p->search("}");
        $string = $p->possible(T_ENCAPSED_AND_WHITESPACE);

        if (!$string->isValid()) {
          $p->strict('"');
        }


        if ($p->isValid()) {
          $start->setValue('".');
          $end->setValue('."');

        }

      }


      $collection[0]->remove();
      $this->assertEquals($expectCode, (string) $collection);

    }

  }
