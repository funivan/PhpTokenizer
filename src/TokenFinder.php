<?php

declare(strict_types=1);

namespace Funivan\PhpTokenizer;

use Funivan\PhpTokenizer\Query\QueryInterface;

/**
 * Simple token finder
 * You can pass query to search tokens in collection
 *
 * For example find all echo values
 *
 * ```
 * $finder = new TokenFinder($collection)
 * $items = $finder->find((new Query())->valueIs('echo'));
 * ```
 */
class TokenFinder
{

    /**
     * @var Collection
     */
    private $collection;


    /**
     * @param Collection $collection
     */
    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }


    /**
     * @param QueryInterface $query
     * @return Collection
     */
    public function find(QueryInterface $query): Collection
    {
        $result = new Collection();
        foreach ($this->collection as $token) {
            if ($query->isValid($token)) {
                $result[] = $token;
            }
        }
        return $result;
    }

}