<?

  namespace Funivan\PhpTokenizer;

  /**
   *
   */
  class Finder {

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var
     */
    private $position = 0;

    /**
     * @param Collection $collection
     */
    public function __construct(Collection $collection) {
      $this->collection = $collection;
    }


    /**
     * Return new StreamProcess for token validation
     *
     * @param boolean $skipWhitespaces
     * @return StreamProcess
     */
    public function iterate($skipWhitespaces = false) {

      if (isset($this->collection[$this->position]) === false) {
        return null;
      }

      $q = new \Funivan\PhpTokenizer\StreamProcess($this->collection, $this->position, $skipWhitespaces);

      ++$this->position;

      return $q;
    }

  }