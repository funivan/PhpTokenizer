<?php

  namespace Funivan\PhpTokenizer\Extractor;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 3/11/15
   */
  class TokenSequenceExtractor implements ExtractorInterface {

    /**
     * @var null
     */
    protected $child = null;

    public function create() {
      return new static();
    }

    public function with(ExtractorInterface $extractor = null) {
      $this->child = $extractor;
      return $this;
    }

    /**
     * @param \Funivan\PhpTokenizer\Collection $collection
     * @return \Funivan\PhpTokenizer\Collection[]
     */
    public function extract(\Funivan\PhpTokenizer\Collection $collection) {
      
      $ranges = $this->getRangeList();
      
    }
    
    public function extractInRange(\Funivan\PhpTokenizer\Collection $collection, $range){
      
      $items = $collection->extractItems($range->from, $range->to);
      
      foreach($items as $token){
        
      }
    }
    

  }