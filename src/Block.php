<?php

  namespace Funivan\PhpTokenizer;

  /**
   * Represent array of collections
   *
   * @method \Funivan\PhpTokenizer\Collection getLast();
   * @method \Funivan\PhpTokenizer\Collection getFirst();
   * @method \Funivan\PhpTokenizer\Collection current();
   * @method \Funivan\PhpTokenizer\Collection[] getItems();
   * @method \Funivan\PhpTokenizer\Collection extractItems($offset, $length = null);
   *
   * @package Funivan\PhpTokenizer
   */
  class Block extends \Fiv\Collection\ObjectCollection {

    /**
     * Used for validation
     *
     * @return string
     */
    public function objectsClassName() {
      return Collection::N;
    }

    /**
     * For each token in collection apply callback
     *
     * ```php
     * //Remove fist token in all collections
     * $block->mapCollection(function(Token $item, $index, Collection $collection){
     *   if ( $index == 1 ) {
     *     $item->remove();
     *   }
     * })
     * ```
     *
     * @param callback $callback
     * @return $this
     * @throws \Funivan\PhpTokenizer\Exception\InvalidArgumentException
     */
    public function mapCollectionTokens($callback) {

      if (!is_callable($callback)) {
        throw new Exception\InvalidArgumentException('Invalid callback function');
      }

      foreach ($this as $collection) {
        $collection->map($callback);
      }

      return $this;
    }
  }

