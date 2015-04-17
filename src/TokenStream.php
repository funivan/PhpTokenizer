<?

  namespace Funivan\PhpTokenizer;

  use Funivan\PhpTokenizer\Exception\InvalidArgumentException;

  /**
   *
   */
  class TokenStream {

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
    public function iterate() {

      if (isset($this->collection[$this->position]) === false) {
        return null;
      }

      $q = new StreamProcess\DefaultStreamProcess($this->collection, $this->position, $this->skipWhitespaces);

      ++$this->position;

      return $q;
    }

  }