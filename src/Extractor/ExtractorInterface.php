<?php

  namespace Funivan\PhpTokenizer\Extractor;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 3/11/15
   */
  interface ExtractorInterface {

    /**
     * @param ExtractorInterface $extractor
     * @param null $name
     * @return mixed
     */
    public function with(ExtractorInterface $extractor = null, $name = null);

    /**
     * @param \Funivan\PhpTokenizer\Collection $collection
     * @param null $name
     * @return \Funivan\PhpTokenizer\Collection[]
     */
    public function extract(\Funivan\PhpTokenizer\Collection $collection, $name = null);

  }