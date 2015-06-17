<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer\Strategy;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Strategy\Move;
  use Funivan\PhpTokenizer\StreamProcess\StreamProcess;
  use Funivan\PhpTokenizer\Token;

  /**
   *
   * @package Test\Funivan\PhpTokenizer\Tokenizer\Query\Strategy
   */
  class MoveTest extends \Test\Funivan\PhpTokenizer\MainTestCase {

    public function testMove() {
      $code = '<?php  $a';

      $finder = new StreamProcess(Collection::initFromString($code));

      $token = $finder->process(Move::create(0));
      $this->assertEquals('<?php ', $token->getValue());

      $token = $finder->process(Move::create(2));
      $this->assertEquals('$a', $token->getValue());

      $token = $finder->process(Move::create(-2));
      $this->assertEquals('<?php ', $token->getValue());

      $token = $finder->process(Move::create(10));
      $this->assertEquals(Token::INVALID_VALUE, $token->getValue());
      $this->assertFalse($token->isValid());

    }

    /**
     * @expectedException \Funivan\PhpTokenizer\Exception\InvalidArgumentException
     */
    public function testInvalidStepsValue() {
      Move::create(null);
    }

    public function testMoveToTokenIndex() {
      $code = '<?php echo $a;';

      $collection = Collection::initFromString($code);
      $newCollection = $collection->extractItems(1);

      $finder = new StreamProcess($newCollection);

      $this->assertEquals('$a', $finder->moveTo(3)->getValue());
      $this->assertEquals(null, $finder->moveTo(14)->getValue());
    }

  }
