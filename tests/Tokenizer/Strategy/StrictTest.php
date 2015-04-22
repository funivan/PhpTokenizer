<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer\Strategy;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Strategy\Strict;
  use Funivan\PhpTokenizer\StreamProcess\StreamProcess;

  class StrictTest extends \Test\Funivan\PhpTokenizer\MainTestCase {


    /**
     *
     */
    public function testSimple() {

      $code = '<?php $a';

      $finder = new StreamProcess(Collection::initFromString($code));

      foreach ($finder as $q) {
        $token = $q->process(Strict::create()->typeIs(T_VARIABLE));
      }

      $this->assertEquals('$a', $token->getValue());

    }
  }
