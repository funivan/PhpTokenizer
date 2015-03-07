<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 11/25/13
   */
  class TokenTest extends \Test\Funivan\PhpTokenizer\Main {

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

      $this->assertCount(3, $firstToken->getData());

      unlink($file->getPath());
    }


    public function testToString() {
      $file = $this->initFileWithCode('<?php echo 1');
      $firstToken = $file->getCollection()->getFirst();

      $this->assertEquals('<?php ', (string) $firstToken);

    }

  }
