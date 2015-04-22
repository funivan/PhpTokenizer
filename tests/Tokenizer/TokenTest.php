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

      $this->assertEquals(token_name(T_LNUMBER), $lastToken->getTypeName());

      unlink($file->getPath());
    }

    public function testSetType() {

      $file = $this->initFileWithCode('<?php echo 1');
      $lastToken = $file->getCollection()->getLast();

      $lastToken->setType(T_WHITESPACE);
      $this->assertEquals(T_WHITESPACE, $lastToken->getType());

      unlink($file->getPath());
    }

    public function testGetData() {

      $file = $this->initFileWithCode('<?php echo 1');
      $firstToken = $file->getCollection()->getFirst();

      $this->assertCount(4, $firstToken->getData());

      unlink($file->getPath());
    }


    public function testToString() {
      $file = $this->initFileWithCode('<?php echo 1');
      $firstToken = $file->getCollection()->getFirst();

      $this->assertEquals('<?php ', (string) $firstToken);

      unlink($file->getPath());
    }

    public function testAssemble() {
      $token = new Token();
      $token->setValue('123');

      $this->assertEquals('123', $token->assemble());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidTokenType() {
      new Token(array(1 => 1));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidValue() {
      new Token(array(0 => 1));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidLine() {
      new Token(array(1, "test"));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidIndex() {
      new Token(array(1, "test", 1, new \stdClass()));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testPrependToValueInvalid() {
      $token = new Token();
      $token->prependToValue(null);
    }

    public function testPrependToValue() {
      $token = new Token();
      $token->setValue("123")->prependToValue("test");
      $this->assertEquals("test123", $token->getValue());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAppendToValueInvalidValue() {
      $token = new Token();
      $token->appendToValue(new \stdClass());
    }

    public function testAppendToValue() {
      $token = new Token();
      $token->setValue("123")->appendToValue("test");
      $this->assertEquals("123test", $token->getValue());
    }

    public function testTokenData() {
      $token = new Token(array(1, "test", 1, 1));

      $this->assertEquals(array(1, "test", 1, 1), $token->getData());
      $this->assertEquals(1, $token->getIndex());
      $this->assertEquals(1, $token->getLine());
      $this->assertEquals(1, $token->getType());
    }

  }
