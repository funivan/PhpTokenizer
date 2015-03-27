<?php

  namespace Funivan\PhpTokenizer\Query\QueryProcessor;

  use Funivan\PhpTokenizer\Collection;

  /**                                                  
   * 
   * @package Funivan\PhpTokenizer\Query\QueryProcessor
   */
  interface QueryProcessorInterface {

    /**
     * @param Collection $collection
     * @param int $currentIndex
     * @return \Funivan\PhpTokenizer\Query\QueryProcessor\QueryProcessorResult
     */
    public function process(Collection $collection, $currentIndex);

  }