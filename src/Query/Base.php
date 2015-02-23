<?php

  namespace Funivan\PhpTokenizer\Query;

  use Funivan\PhpTokenizer;

  /**
   *
   * @author Ivan Shcherbak <dev@funivan.com>
   */
  abstract class Base {

    /**
     * @var PhpTokenizer\Collection
     */
    protected $collection = null;

    /**
     * @var bool
     */
    protected $cache = null;

    /**
     * @param PhpTokenizer\Collection $collection
     */
    public function __construct(PhpTokenizer\Collection $collection = null) {
      $this->collection = $collection;
    }

    /**
     * @codeCoverageIgnore
     * @return bool
     */
    protected abstract function parse();

    /**
     * Clean cache
     *
     * @return $this
     */
    public function cleanCache() {
      $this->cache = null;
      return $this;
    }

  }