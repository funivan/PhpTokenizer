<?
  namespace Funivan\PhpTokenizer;

  use Funivan\PhpTokenizer\Exception\InvalidArgumentException;
  use Funivan\PhpTokenizer\Strategy\StrategyInterface;
  use Funivan\PhpTokenizer\Strategy\Strict;
  use Funivan\PhpTokenizer\Token\Range;
  use Funivan\PhpTokenizer\Token\VirtualToken;

  /**
   * Start from specific position and check token from this position according to stategies
   *
   */
  class StreamProcess {

    private $position;

    /**
     * @var \Funivan\PhpTokenizer\Collection
     */
    private $collection;

    /**
     * @var bool
     */
    private $valid = true;

    /**
     * @var bool
     */
    private $skipWhitespaces = false;

    /**
     * @param Collection $collection
     * @param $position
     * @param bool $skipWhitespaces
     */
    public function __construct(\Funivan\PhpTokenizer\Collection $collection, $position, $skipWhitespaces = false) {
      $this->position = $position;
      $this->collection = $collection;
      $this->skipWhitespaces = $skipWhitespaces;
    }


    /**
     * Alias
     * @param string $value
     * @return Token
     */
    public function valueIs($value) {
      $strict = new Strict();
      $strict->valueIs($value);
      return $this->check($strict);
    }

    /**
     * @param $value
     * @return Token
     */
    public function typeIs($value) {
      $strict = new Strict();
      $strict->typeIs($value);
      return $this->check($strict);
    }

    /**
     * @param string $start
     * @param string $end
     * @return Token
     */
    public function section($start, $end) {
      $section = new Strategy\Section();
      $section->setDelimiters($start, $end);
      return $this->check($section);
    }

    /**
     * Indicate if our conditions is valid
     * @return bool
     */
    public function isValid() {
      return ($this->valid === true);
    }

    /**
     * @param StrategyInterface $strategy
     * @return Token
     */
    public function check(StrategyInterface $strategy) {

      if ($this->isValid() === false) {
        return VirtualToken::create();
      }

      $result = $strategy->process($this->collection, $this->position);

      if ($result->isValid() == false) {
        $this->valid = false;
        return VirtualToken::create();
      }


      $this->position = $result->getNexTokenIndex();


      $token = $result->getToken();
      if ($token === null) {
        $token = new VirtualToken();
      }
      
      if ($this->skipWhitespaces and isset($this->collection[$this->position]) and $this->collection[$this->position]->getType() === T_WHITESPACE) {
        # skip whitespaces in next check
        $this->position++;
      }

      return $token;
    }

    public function search($string) {
      $section = new Strategy\Search();
      $section->valueIs($string);
      return $this->check($section);
    }

    /**
     * @param array $tokenValues
     * @return Range
     */
    public function sequence(array $tokenValues) {
      $range = new Range();
      foreach ($tokenValues as $value) {
        if (is_string($value) or $value === null) {
          $query = new Strategy\Strict();
          $query->valueIs($value);
        } elseif (is_int($value)) {
          $query = new Strategy\Strict();
          $query->typeIs($value);
        } elseif ($value instanceof StrategyInterface) {
          $query = $value;
        } else {
          throw new InvalidArgumentException("Invalid token Values sequence");
        }

        $token = $this->check($query);
        $range->add($token);
      }

      return $range;
    }

  }