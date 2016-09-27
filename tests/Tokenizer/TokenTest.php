<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer;

  use Funivan\PhpTokenizer\Exception\InvalidArgumentException;
  use Funivan\PhpTokenizer\Token;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 11/25/13
   */
  class TokenTest extends \Test\Funivan\PhpTokenizer\MainTestCase {

    public function testGetTypeName() {

      $file = $this->initFileWithCode('<?php echo 1');
      $lastToken = $file->getCollection()->getLast();

      static::assertEquals(token_name(T_LNUMBER), $lastToken->getTypeName());

      unlink($file->getPath());
    }


    public function testSetType() {

      $file = $this->initFileWithCode('<?php echo 1');
      $lastToken = $file->getCollection()->getLast();

      $lastToken->setType(T_WHITESPACE);
      static::assertEquals(T_WHITESPACE, $lastToken->getType());

      unlink($file->getPath());
    }


    public function testGetData() {

      $file = $this->initFileWithCode('<?php echo 1');
      $firstToken = $file->getCollection()->getFirst();

      static::assertCount(4, $firstToken->getData());

      unlink($file->getPath());
    }


    public function testToString() {
      $file = $this->initFileWithCode('<?php echo 1');
      $firstToken = $file->getCollection()->getFirst();

      static::assertEquals('<?php ', (string) $firstToken);

      unlink($file->getPath());
    }


    public function testAssemble() {
      $token = new Token();
      $token->setValue('123');

      static::assertEquals('123', $token->assemble());
    }


    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidTokenType() {
      new Token([1 => 1]);
    }


    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidValue() {
      new Token([0 => 1]);
    }


    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidLine() {
      new Token([1, 'test']);
    }


    public function testAppendToValue() {
      $token = new Token();
      $token->setValue('123')->appendToValue('test');
      static::assertEquals('123test', $token->getValue());
    }


    public function testTokenData() {
      $token = new Token([1, 'test', 1, 1]);

      static::assertEquals([1, 'test', 1, 1], $token->getData());
      static::assertEquals(1, $token->getIndex());
      static::assertEquals(1, $token->getLine());
      static::assertEquals(1, $token->getType());
    }


    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetValue() {
      $token = new Token();
      static::assertNull($token->getValue());
      $token->setValue('a');
      static::assertSame('a', $token->getValue());


      $token->setValue(1);
      static::assertSame('1', $token->getValue());

      $token->setValue(null);
    }


    /**
     * @expectedException InvalidArgumentException
     */
    public function testPrependToTheValue() {
      $token = new Token();
      static::assertNull($token->getValue());
      $token->setValue('123');
      static::assertSame('123', $token->getValue());

      $token->prependToValue('start');
      static::assertSame('start123', $token->getValue());

      $token->prependToValue(9);
      static::assertSame('9start123', $token->getValue());

      $token->prependToValue(null);
    }


    /**
     * @expectedException InvalidArgumentException
     */
    public function testAppendToTheValue() {
      $token = new Token();
      $token->setValue('123');
      static::assertSame('123', $token->getValue());

      $token->appendToValue('start');
      static::assertSame('123start', $token->getValue());

      $token->appendToValue(1);
      static::assertSame('123start1', $token->getValue());
      $token->prependToValue(null);
    }

  }
