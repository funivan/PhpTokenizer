<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer\Strategy;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Finder;
  use Funivan\PhpTokenizer\Strategy\Strict;

  class StrictTest extends \Test\Funivan\PhpTokenizer\Main {


    public function testSimple() {

      $code = '<? $a';

      $finder = new Finder(Collection::initFromString($code));

      while ($q = $finder->iterate()) {
        $token = $q->check(Strict::create()->typeIs(T_VARIABLE));
      }

      $this->assertEquals('$a', $token->getValue());

    }
  }
