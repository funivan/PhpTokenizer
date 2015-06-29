<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer\Strategy;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Strategy\Strict;

  class StrictTest extends \Test\Funivan\PhpTokenizer\MainTestCase {


    /**
     *
     */
    public function testSimple() {

      $code = '<?php echo $a; foreach($users as $user){}';

      $variables = array();
      $collection = Collection::initFromString($code);

      $query = Strict::create()->typeIs(T_VARIABLE);

      foreach ($collection as $index => $token) {

        if ($query->isValid($token)) {
          $variables[] = $token;
        }

      }

      $this->assertCount(3, $variables);

    }
  }
