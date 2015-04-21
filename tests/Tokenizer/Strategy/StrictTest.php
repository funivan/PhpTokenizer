<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer\Strategy;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Strategy\Strict;
  use Funivan\PhpTokenizer\StreamProcess\DefaultStreamProcess;

  class StrictTest extends \Test\Funivan\PhpTokenizer\MainTestCase {


    /**
     *
     */
    public function testSimple() {

      $code = '<? $a';

      $finder = new DefaultStreamProcess(Collection::initFromString($code));

      foreach ($finder as $q) {
        $token = $q->process(Strict::create()->typeIs(T_VARIABLE));
      }

      $this->assertEquals('$a', $token->getValue());

    }
  }
