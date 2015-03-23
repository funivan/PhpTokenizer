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
     * @return string
     */
    public function getName();

    /**
     * @return ExtractorInterface|null
     */
    public function getChild();

    /**
     * @param \Funivan\PhpTokenizer\Collection $collection
     * @return ExtractorResult[]
     */
    public function getRangeList(\Funivan\PhpTokenizer\Collection $collection);

  }