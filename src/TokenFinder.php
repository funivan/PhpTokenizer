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
    public function __construct(
        private readonly Collection $collection
    ) {
    }

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
