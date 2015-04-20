<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer\Strategy;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Strategy\Strict;
  use Funivan\PhpTokenizer\StreamIterator;

  class StrictTest extends \Test\Funivan\PhpTokenizer\MainTestCase {


    public function testSimple() {

      $code = '<? $a';

      $finder = new StreamIterator(Collection::initFromString($code));

      while ($q = $finder->getProcessor()) {
        $token = $q->process(Strict::create()->typeIs(T_VARIABLE));
      }

      $this->assertEquals('$a', $token->getValue());

    }
  }
