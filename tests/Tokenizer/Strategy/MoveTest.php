<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer\Strategy;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Strategy\Move;
  use Funivan\PhpTokenizer\StreamIterator;
  use Funivan\PhpTokenizer\Token;

  /**
   *
   * @package Test\Funivan\PhpTokenizer\Tokenizer\Query\Strategy
   */
  class MoveTest extends \Test\Funivan\PhpTokenizer\MainTestCase {

    public function testMove() {
      $code = '<? $a';

      $finder = new StreamIterator(Collection::initFromString($code));

      $q = $finder->getProcessor();
      $token = $q->process(Move::create(0));
      $this->assertEquals('<?', $token->getValue());

      $token = $q->process(Move::create(2));
      $this->assertEquals('$a', $token->getValue());

      $token = $q->process(Move::create(-2));
      $this->assertEquals('<?', $token->getValue());

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
