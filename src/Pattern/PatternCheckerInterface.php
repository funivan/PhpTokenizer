<?php
  
  namespace Funivan\PhpTokenizer\Pattern;

  use Funivan\PhpTokenizer\Collection;


  /**
   * Check collection according to callback patterns
   *
   */
  interface PatternCheckerInterface {

    /**
     * @param callable $pattern
     * @return $this
     * @throws \Exception
     */
    public function apply(callable $pattern);

    /**
     *
     * @return Collection[]
     */
    public function getCollections();
  }