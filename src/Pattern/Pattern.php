<?php

  namespace Funivan\PhpTokenizer\Pattern;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Exception\Exception;
  use Funivan\PhpTokenizer\QuerySequence\QuerySequence;

  /**
   * Apply pattern to collection
   */
  class Pattern implements PatternCheckerInterface {

    /**
     * @var array
     */
    protected $collections = array();

    /**
     *
     * @param Collection $collection
     */
    public function __construct(Collection $collection) {
      $this->collections[] = $collection;
    }


    /**
     * @inheritdoc
     */
    public function apply(callable $pattern) {

      # Clear current collections.
      # We will add new one and iterate over current

      $collections = $this->collections;
      $this->collections = array();

      foreach ($collections as $collection) {

        $collectionsResult = $this->iterateOverCollections($pattern, $collection);

        if ($collectionsResult === null) {
          $collectionsResult = array();
        }

        if (!is_array($collectionsResult)) {
          throw new Exception('Invalid result from pattern callback. Expect null or array of collections');
        }

        foreach ($collectionsResult as $resultCollection) {
          if (!($resultCollection instanceof Collection)) {
            throw new Exception("Invalid result from pattern callback. Expect array of collections");
          }
        }


        foreach ($collectionsResult as $resultCollection) {
          $this->collections[] = $resultCollection;
        }

      }

      return $this;
    }


    /**
     * @inheritdoc
     */
    public function getCollections() {
      return $this->collections;
    }


    /**
     * @param callable $pattern
     * @param Collection $collection
     * @return mixed
     */
    protected function iterateOverCollections(callable $pattern, Collection $collection) {
      $result = array();
      foreach ($collection as $index => $token) {
        $querySequence = new QuerySequence($collection, $index);
        $patternResult = $pattern($querySequence);
        if ($patternResult === null) {
          continue;
        }

        $result[] = $patternResult;
      }

      return $result;
    }

  }