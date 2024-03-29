<?php

declare(strict_types=1);

namespace Funivan\PhpTokenizer;

use ArrayAccess;
use Countable;
use Funivan\PhpTokenizer\Exception\Exception;
use Funivan\PhpTokenizer\Query\Query;
use InvalidArgumentException;
use Iterator;
use Stringable;

class Collection implements Iterator, ArrayAccess, Countable, Stringable
{
    /**
     * @var int
     */
    protected $position = 0;

    /**
     * Array of objects
     *
     * @var array
     */
    protected $items = [];

    /**
     * @var string
     */
    protected $initialContentHash;

    public function __construct(array $items = [])
    {
        if (! empty($items)) {
            $this->setItems($items);
        }
        $this->storeContentHash();
    }

    public function __clone()
    {
        $items = [];
        foreach ($this->items as $item) {
            $items[] = $item;
        }
        $this->setItems($items);
    }

    /**
     * Extract each value from token
     */
    public function __toString(): string
    {
        return $this->assemble();
    }

    /**
     * @param string $string
     * @throws Exception
     */
    public static function createFromString($string): Collection
    {
        $tokens = Helper::getTokensFromString($string);
        return new Collection($tokens);
    }

    /**
     * Return number of items in this collection
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Add one item to begin of collection
     * This item is accessible via `$collection->getFirst();`
     *
     * @return $this
     */
    public function prepend(Token $item): self
    {
        array_unshift($this->items, $item);
        return $this;
    }

    /**
     * Add one item to the end of collection
     * This item is accessible via `$collection->getLast();`
     *
     * @return $this
     */
    public function append(Token $item): self
    {
        $this->items[] = $item;
        return $this;
    }

    /**
     * @param int $index
     * @param array $items
     * @return $this
     * @throws InvalidArgumentException
     */
    public function addAfter($index, $items): self
    {
        if (! is_array($items)) {
            throw new InvalidArgumentException('You can add after only array of items');
        }

        foreach ($items as $item) {
            if (! ($item instanceof Token)) {
                throw new InvalidArgumentException('Expect array of tokens. Token[]');
            }
        }

        if (! is_int($index)) {
            throw new InvalidArgumentException('Invalid type of index. Must be integer');
        }

        $offset = $index + 1;
        $firstPart = array_slice($this->items, 0, $offset);
        $secondPart = array_slice($this->items, $offset);
        $this->items = array_merge($firstPart, $items, $secondPart);
        return $this;
    }

    /**
     * Truncate current list of items and add new
     * @param Token[] $items
     * @return $this
     */
    public function setItems(array $items): self
    {
        foreach ($items as $item) {
            if (! ($item instanceof Token)) {
                throw new InvalidArgumentException('Expect array of tokens. Token[]');
            }
        }

        $this->items = $items;
        $this->rewind();
        return $this;
    }

    /**
     * Remove part of items from collection
     * Works as array_slice
     *
     * @return $this
     */
    public function slice(int $offset, int $length = null): self
    {
        $this->items = array_slice($this->items, $offset, $length);
        return $this;
    }

    /**
     * Take part of items and return new collection
     * Works as array_slice
     * At this point items in 2 collection is same
     *
     * @param null $length
     */
    public function extractItems(int $offset, $length = null): Collection
    {
        $items = array_slice($this->items, $offset, $length);
        return new Collection($items);
    }

    /**
     * Rewind current collection
     */
    public function rewind(): void
    {
        $this->position = 0;
        $this->items = array_values($this->items);
    }

    public function getLast(): ?Token
    {
        $lastToken = end($this->items);
        return ($lastToken !== false) ? $lastToken : null;
    }

    /**
     * Return first item from collection
     */
    public function getFirst(): ?Token
    {
        $first = reset($this->items);
        return $first !== false ? $first : null;
    }

    /**
     * Return next item from current
     * Also can return item with position from current + $step
     */
    public function getNext(int $step = 1): Token
    {
        $position = ($this->position + $step);
        return $this->items[$position] ?? new Token();
    }

    /**
     * Return previous item
     * Also can return previous from current position + $step
     */
    public function getPrevious(int $step = 1): Token
    {
        $position = ($this->position - $step);
        return ($this->items[$position]) ?? new Token();
    }

    /**
     * Return current item in collection
     */
    public function current(): Token
    {
        return $this->items[$this->position];
    }

    /**
     * Return current position
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * Switch to next position
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * Check if item exist in current position
     */
    public function valid(): bool
    {
        return isset($this->items[$this->position]);
    }

    /**
     * Add item to the end or modify item with given key
     *
     * @param int|null $offset
     * @param Token $item
     */
    public function offsetSet($offset, $item): void
    {
        if (! ($item instanceof Token)) {
            throw new InvalidArgumentException('Expect Token object');
        }

        if (null === $offset) {
            $this->append($item);
            return;
        }

        if (! is_int($offset)) {
            throw new InvalidArgumentException('Invalid type of index. Must be integer');
        }
        $this->items[$offset] = $item;
    }

    /**
     * Check if item with given offset exists
     *
     * @param int $offset
     */
    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * Remove item from collection
     *
     * @param int $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->items[$offset]);
    }

    /**
     * Get item from collection
     *
     * @param int $offset
     */
    public function offsetGet($offset): ?Token
    {
        return $this->items[$offset] ?? null;
    }

    /**
     * Return array of items connected to this collection
     *
     * Rewrite this method in you class
     *
     * <code>
     * foreach($collection->getTokens() as $item){
     *  echo get_class($item)."\n;
     * }
     * </code>
     * @return Token[]
     */
    public function getTokens(): array
    {
        return $this->items;
    }

    /**
     * Iterate over objects in collection
     *
     * <code>
     * $collection->each(function($item, $index, $collection){
     *    if ( $index > 0 ) {
     *      $item->remove();
     *    }
     * })
     * </code>
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function each(callable $callback): self
    {
        if (! is_callable($callback)) {
            throw new InvalidArgumentException('Invalid callback function');
        }

        foreach ($this->getTokens() as $index => $item) {
            call_user_func($callback, $item, $index, $this);
        }

        $this->rewind();

        return $this;
    }

    /**
     * Remove all tokens in collection
     *
     * @return $this
     */
    public function remove(): self
    {
        foreach ($this as $token) {
            $token->remove();
        }
        return $this;
    }

    public function find(Query $query): Collection
    {
        $finder = new TokenFinder($this);
        return $finder->find($query);
    }

    /**
     * Remove all invalid tokens in collection
     * Refresh index.
     */
    public function refresh(): self
    {
        $string = $this->assemble();
        $this->cleanCollection();

        $tokens = Helper::getTokensFromString($string);
        $this->setItems($tokens);

        $this->rewind();
        return $this;
    }

    public function extractByTokens(Token $tokenStart, Token $tokenEnd): Collection
    {
        $collection = new Collection();
        $startIndex = $tokenStart->getIndex();
        $endIndex = $tokenEnd->getIndex();

        foreach ($this->getTokens() as $token) {
            if ($token->getIndex() >= $startIndex and $token->getIndex() <= $endIndex) {
                $collection->append($token);
            }
        }

        return $collection;
    }

    /**
     * @return $this
     */
    public function storeContentHash(): self
    {
        $this->initialContentHash = $this->getContentHash();
        return $this;
    }

    public function isChanged(): bool
    {
        return ($this->getContentHash() !== $this->initialContentHash);
    }

    private function getContentHash(): string
    {
        return md5($this->assemble());
    }

    public function assemble(): string
    {
        $string = '';
        /** @var Token $token */
        foreach ($this as $token) {
            if (! $token->isValid()) {
                continue;
            }
            $string .= $token->getValue();
        }

        return $string;
    }

    /**
     * Remove invalid tokens from collection
     *
     * @return $this
     */
    protected function cleanCollection(): self
    {
        foreach ($this as $index => $token) {
            if ($token->isValid()) {
                continue;
            }
            unset($this->items[$index]);
        }

        return $this;
    }
}
