<?php

  declare(strict_types=1);

  namespace Funivan\PhpTokenizer;

  use Funivan\PhpTokenizer\Query\Query;

  /**
   * Simple toke  finder
   * You can pass query to search tokens in collection
   *
   * For example find all echo values
   *
   * ```
   * $finder = new TokenFinder($collection)
   * $items = $finder->find((new Query())->valueIs('echo'));
   *
   * ```
   *
   * @author Ivan Shcherbak <dev@funivan.com> 4/17/15
   */
  class TokenFinder {

    /**
     * @var Collection
     */
    private $collection;


    /**
     * @param Collection $collection
     */
    public function __construct(Collection $collection) {
      $this->collection = $collection;
    }


    /**
     * @param Query $query
     * @return Collection
     */
    public function find(Query $query) : Collection {
      $result = new Collection();

      foreach ($this->collection as $token) {
        if ($query->isValid($token)) {
          $result[] = $token;
        }
      }

      return $result;

    }

  }