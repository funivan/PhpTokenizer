<?php

  namespace Funivan\PhpTokenizer\Extractor;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 3/11/15
   */
  interface ExtractorInterface {

    /**
     * @param ExtractorInterface $extractor
     * @return mixed
     */
    public function with(ExtractorInterface $extractor = null);

    /**
     * @param \Funivan\PhpTokenizer\Collection $collection
     * @return \Funivan\PhpTokenizer\Collection[]
     */
    public function extract(\Funivan\PhpTokenizer\Collection $collection);

  }