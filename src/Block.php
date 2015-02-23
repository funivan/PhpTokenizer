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
     * <code>
     * //Remove fist token in all collections
     * $block->mapCollection(function($item, $index, $collection){
     *   if ( $index == 1 ) {
     *     $item->remove();
     *   }
     * })
     * </code>
     *
     * @param callback $callback
     * @return $this
     * @throws \Funivan\PhpTokenizer\Exception
     */
    public function mapCollection($callback) {

      if (!is_callable($callback)) {
        throw new \Funivan\PhpTokenizer\Exception('Invalid callback function');
      }

      foreach ($this as $collection) {
        $collection->map($callback);
      }

      return $this;
    }
  }

