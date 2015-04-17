<?

  namespace Funivan\PhpTokenizer\Token;

  use Funivan\PhpTokenizer\Token;

  /**
   *
   * @package Funivan\PhpTokenizer\Token
   */
  class Range {

    /**
     * @var array
     */
    private $items = [];

    /**
     * @var bool
     */
    private $valid = true;


    /**
     * @param Token $token
     * @return $this
     */
    public function add(Token $token) {
      $this->items[] = $token;
      if ($token->isValid() === false) {
        $this->valid = false;
      }

      return $this;
    }

    /**
     * @return int
     */
    public function count() {
      return count($this->items);
    }

    /**
     * @return bool
     */
    public function isValid() {
      return $this->valid;
    }

    /**
     * @return Token|null
     */
    public function getFirst() {
      return reset($this->items);

    }

    /**
     * @return Token|null
     */
    public function getLast() {
      return end($this->items);
    }

    /**
     * @param int $int
     * @return Token|null
     */
    public function get($int) {
      return isset($this->items[$int]) ? $this->items[$int] : new VirtualToken();
    }

    public function __toString() {
      $result = "";

      /** @var Token $item */
      foreach ($this->items as $item) {
        $result = $result . $item->assemble();
      }
      return $result;
    }

  }