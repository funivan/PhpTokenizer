<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer\Strategy;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\TokenStream;
  use Funivan\PhpTokenizer\Strategy\Strict;

  class StrictTest extends \Test\Funivan\PhpTokenizer\MainTestCase {


    public function testSimple() {

      $code = '<?php $a';

      $finder = new TokenStream(Collection::initFromString($code));

      while ($q = $finder->iterate()) {
        $token = $q->process(Strict::create()->typeIs(T_VARIABLE));
      }

      $this->assertEquals('$a', $token->getValue());

    }
  }
