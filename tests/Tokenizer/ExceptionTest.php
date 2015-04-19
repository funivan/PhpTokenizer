<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer;

  use Funivan\PhpTokenizer\Token;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 11/25/13
   */
  class ExceptionTest extends \Test\Funivan\PhpTokenizer\MainTestCase {

    public function testSetTokenValueException() {
      $token = new Token();

      try {

        $token->setValue((object) array());

      } catch (\Exception $e) {
        $this->assertInstanceOf('\Funivan\PhpTokenizer\Exception\Exception', $e);
        return true;
      }

      $this->fail('Set invalid token value. Expect exception.');
    }

  }
