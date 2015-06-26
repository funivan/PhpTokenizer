<?php

  namespace Funivan\PhpTokenizer\StreamProcess;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Exception\Exception;

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

    /**
     *
     * @param Collection $collection
     */
    public function __construct($collection) {
      $this->collections[] = $collection;
    }


    /**
     * @param callable $pattern
     * @return $this
     * @throws \Exception
     */
    public function pattern(callable $pattern) {

      # Clear current collections.
      # We will add new one and iterate over current

      $collections = $this->collections;
      $this->collections = array();

      foreach ($collections as $collection) {

        $processor = $this->createStreamProcessor($collection);

        $collectionsResult = $pattern($processor);
        if ($collectionsResult !== null and !is_array($collectionsResult)) {
          throw new Exception('Invalid result from pattern callback. Expect null or array of collections');
        }

        foreach ($collectionsResult as $resultCollection) {
          if (!($resultCollection instanceof Collection)) {
            throw new Exception("Invalid result from pattern callback. Expect array of collections");
          }
          
          $this->collections[] = $resultCollection;
        }
      }

      return $this;
    }

    /**
     *
     * @return Collection[]
     */
    public function getCollections() {
      return $this->collections;
    }

    /**
     * @param Collection $collection
     * @return StreamProcess
     */
    protected function createStreamProcessor(Collection $collection) {
      $processor = new StreamProcess($collection, true);
      return $processor;
    }

  }