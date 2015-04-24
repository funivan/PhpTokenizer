<?php

  namespace Test\Funivan\PhpTokenizer\Demo;

  /**
   *
   * @author Ivan Shcherbak <dev@funivan.com>
   */
  class ConcatenationOptimizeTest {

    const MAX_ITERATION = 10;

    /**
     *
     * OLD: echo "$user";
     * NEW: echo $user;
     *
     * @param \Funivan\PhpTokenizer\Collection $collection
     * @return bool
     */
    public function removeQuotesNearVariable(\Funivan\PhpTokenizer\Collection $collection) {

      $changed = false;

      return $changed;
    }


  }