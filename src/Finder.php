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
     * @return \Funivan\PhpTokenizer\StreamProcess
     */
    public function iterate() {

      if (isset($this->collection[$this->position]) === false) {
        return null;
      }

      $q = new \Funivan\PhpTokenizer\StreamProcess($this->collection, $this->position);

      ++$this->position;

      return $q;
    }

  }