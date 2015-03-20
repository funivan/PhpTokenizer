<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Token;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 11/25/13
   */
  class BlockTest extends \Test\Funivan\PhpTokenizer\Main {

    public function testBlockMapCollection() {
      $code = <<<CODE
<?php
  function(){
    echo 1;
  }

CODE;

      $file = $this->initFileWithCode($code);

      $collection = $file->getCollection();
      $items = $collection->extractItems(2, 3);

      $block = new \Funivan\PhpTokenizer\Block();
      $block->append($items);

      $block->mapCollectionTokens(function (Token $token, $index, Collection $collection) {
        if ($index == 1) {
          $token->prependToValue(" ");
          $collection->getLast()->prependToValue(" ");
        }
      });


      $itemsNum = $collection->count();
      $collection->refresh();
      $itemsNum = $itemsNum + 2;
      $this->assertCount($itemsNum, $collection);
      unlink($file->getPath());
    }

    /**
     *
     * @expectedException \Funivan\PhpTokenizer\Exception\InvalidArgumentException
     */
    public function testMapCollectionInvalidFunction() {
      $block = new \Funivan\PhpTokenizer\Block();
      $block->mapCollectionTokens(null);

    }
  }
