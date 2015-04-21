<?php

  namespace Funivan\PhpTokenizer;

  use Funivan\PhpTokenizer\Exception\InvalidArgumentException;
  use Funivan\PhpTokenizer\StreamProcess\DefaultStreamProcess;

  /**
   *
   */
  class StreamIterator implements \Iterator {

    /**
     * @var Collection
     */
    private $collection;

    /**
     * Initial position of check
     *
     * @var int
     */
    private $position = 0;

    /**
     * @var bool
     */
    private $skipWhitespaces;

    /**
     * @param Collection $collection
     * @param bool $skipWhitespaces
     */
    public function __construct(Collection $collection, $skipWhitespaces = false) {
      $this->collection = $collection;
      if (!is_bool($skipWhitespaces)) {
        throw new InvalidArgumentException('Invalid whitespace strategy value. Expect boolean');
      }
      $this->skipWhitespaces = $skipWhitespaces;
    }


    /**
     * Return new DefaultStreamProcess for token validation
     *
     * @return \Funivan\PhpTokenizer\StreamProcess\DefaultStreamProcess
     */
    public function getProcessor() {

      if (isset($this->collection[$this->position]) === false) {
        return null;
      }

      $q = new DefaultStreamProcess($this->collection, $this->position, $this->skipWhitespaces);

      ++$this->position;

      return $q;
    }

    /**
     * @inheritdoc
     */
    public function rewind() {
      $this->position = 0;
    }

    /**
     * @inheritdoc
     */
    public function current() {
      return new DefaultStreamProcess($this->collection, $this->position, $this->skipWhitespaces);
    }

    /**
     * @inheritdoc
     */
    public function key() {
      return $this->position;
    }

    /**
     * @inheritdoc
     */
    public function next() {
      ++$this->position;
    }

    /**
     * @inheritdoc
     */
    public function valid() {
      return isset($this->collection[$this->position]);
    }

  }