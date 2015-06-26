<?php

  namespace Funivan\PhpTokenizer\StreamProcess;

  use Funivan\PhpTokenizer\Collection;

  /**
   * Test class. Under development.
   *
   * @author Ivan Shcherbak <dev@funivan.com> 6/26/15
   */
  class TokensChecker {

    /**
     * @var array
     */
    protected $collections = array();

    public function __construct($collections) {
      if ($collections instanceof \Funivan\PhpTokenizer\Collection) {
        $this->collections[] = $collections;
      } else {
        $this->collections = $collections;
      }
    }


    /**
     * @param mixed $param
     * @return $this
     * @throws \Exception
     */
    public function pattern($param) {
      if (!is_callable($param)) {
        throw new \Exception("Invalid param. Expect callable");
      }

      $collections = $this->collections;
      $this->collections = array();
      foreach ($collections as $collection) {
        $processor = new StreamProcess($collection, true);
        $collectionsResult = $param($processor);
        if (is_array($collectionsResult)) {
          foreach ($collectionsResult as $resultCollection) {
            $this->collections[] = $resultCollection;
          }
        } elseif ($collectionsResult instanceof \Funivan\PhpTokenizer\Collection) {
          $this->collections[] = $collectionsResult;
        }
      }

      return $this;
    }

    /**
     * @return Collection[]
     */
    public function getCollections() {
      return $this->collections;
    }

  }