<?php

  declare(strict_types=1);

  namespace Test\Funivan\PhpTokenizer\Tokenizer\Strategy;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Exception\InvalidArgumentException;
  use Funivan\PhpTokenizer\QuerySequence\QuerySequence;
  use Funivan\PhpTokenizer\Strategy\Move;
  use Funivan\PhpTokenizer\Token;

  /**
   *
   *
   */
  class MoveTest extends \PHPUnit_Framework_TestCase {

    /**
     *
     */
    public function testMove() {
      $code = '<?php  $a';

      $finder = new QuerySequence(Collection::createFromString($code));

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
     * @expectedException InvalidArgumentException
     */
    public function testInvalidStepsValue() {
      Move::create(null);
    }


    public function testMoveToToken() {
      $code = '<?php echo $a;';

      $collection = Collection::createFromString($code);

      $finder = new QuerySequence($collection);
      $token = $collection[3];

      $this->assertEquals('$a', $finder->moveToToken($token)->getValue());
      $this->assertTrue($finder->isValid());

      # token is connected to collection
      # so when we modify token index we can find this token in collection
      $token->setIndex(125);
      $this->assertEquals('$a', $finder->moveToToken($token)->getValue());
      $this->assertTrue($finder->isValid());

      # disconnect token from collection
      $token = clone $token;
      $token->setIndex(4525);
      $this->assertEquals(null, $finder->moveToToken($token)->getValue());
      $this->assertFalse($finder->isValid());
    }

  }
