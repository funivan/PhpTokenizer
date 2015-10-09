<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer;

  use Funivan\PhpTokenizer\Collection;
  use Test\Funivan\PhpTokenizer\MainTestCase;

  /**
   *
   * @package Test\Funivan\PhpTokenizer\Tokenizer
   */
  class HelperTest extends MainTestCase {

    /**
     * @void
     */
    public function testCheckLines() {
      $code = '<?php return [
      ];';

      $collection = Collection::createFromString($code);
      $this->assertEquals(2, $collection->getLast()->getLine());

      $code = '<?php 
      
      return [
      ];';

      $collection = Collection::createFromString($code);
      $this->assertEquals(4, $collection->getLast()->getLine());
    }


  }
