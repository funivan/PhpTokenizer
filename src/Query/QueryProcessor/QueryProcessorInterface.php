<?php

  namespace Funivan\PhpTokenizer\Query\QueryProcessor;

  use Funivan\PhpTokenizer\Collection;

  interface QueryProcessorInterface {

    /**
     * @param Collection $collection
     * @param int $currentIndex
     * @return \Funivan\PhpTokenizer\BlockExtractor\ExtractorResult
     */
    public function process(Collection $collection, $currentIndex);

  }