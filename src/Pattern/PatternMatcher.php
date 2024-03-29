<?php

declare(strict_types=1);

namespace Funivan\PhpTokenizer\Pattern;

use Funivan\PhpTokenizer\Collection;
use Funivan\PhpTokenizer\Exception\Exception;
use Funivan\PhpTokenizer\QuerySequence\QuerySequence;

class PatternMatcher implements PatternMatcherInterface
{
    /**
     * @var Collection[]
     */
    protected $collections = [];

    public function __construct(Collection $collection)
    {
        $this->collections[] = $collection;
    }

    public function apply(callable $pattern): self
    {
        # Clear current collections.
        # We will add new one and iterate over current

        $collections = $this->collections;
        $this->collections = [];

        foreach ($collections as $collection) {
            $collectionsResult = $this->iterateOverCollections($pattern, $collection);

            foreach ($collectionsResult as $resultCollection) {
                $this->collections[] = $resultCollection;
            }
        }

        return $this;
    }

    public function getCollections()
    {
        return $this->collections;
    }

    /**
     * @return Collection[]
     * @throws Exception
     */
    protected function iterateOverCollections(callable $pattern, Collection $collection)
    {
        $result = [];

        $collection->rewind();
        foreach ($collection as $index => $token) {
            $querySequence = new QuerySequence($collection, $index);
            $patternResult = $pattern($querySequence);
            if ($patternResult === null) {
                continue;
            }

            if (! ($patternResult instanceof Collection)) {
                throw new Exception('Invalid result from pattern callback. Expect Collection. Given:' . gettype($patternResult));
            }

            $result[] = $patternResult;
        }

        return $result;
    }
}
