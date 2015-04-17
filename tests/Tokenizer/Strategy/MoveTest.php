<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer\Strategy;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Strategy\Move;
  use Funivan\PhpTokenizer\Strategy\Strict;
  use Funivan\PhpTokenizer\Token;
  use Funivan\PhpTokenizer\TokenStream;

  /**
   *
   * @package Test\Funivan\PhpTokenizer\Tokenizer\Query\Strategy
   */
  class MoveTest extends \Test\Funivan\PhpTokenizer\Main {

    public function testMove() {
      $code = '<?php  $a';

      $finder = new TokenStream(Collection::initFromString($code));

      $q = $finder->iterate();
      $token = $q->process(Move::create(0));
      $this->assertEquals('<?php ', $token->getValue());

      $token = $q->process(Move::create(2));
      $this->assertEquals('$a', $token->getValue());

      $token = $q->process(Move::create(-2));
      $this->assertEquals('<?php ', $token->getValue());
                                 
      $token = $q->process(Move::create(10));
      $this->assertEquals(Token::INVALID_VALUE, $token->getValue());
      $this->assertFalse($token->isValid());

    }

    /**
     * @expectedException \Funivan\PhpTokenizer\Exception\InvalidArgumentException
     */
    public function testInvalidStepsValue(){
      Move::create(null);
    }

  }
