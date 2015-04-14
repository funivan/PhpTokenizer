<?
  namespace Funivan\PhpTokenizer;

  use Funivan\PhpTokenizer\Strategy\StrategyInterface;
  use Funivan\PhpTokenizer\Strategy\Strict;
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
    private $skipCondition = false;

    function __construct(\Funivan\PhpTokenizer\Collection $collection, $position) {
      $this->position = $position;
      $this->collection = $collection;
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
    public function valid() {
      return ($this->skipCondition === false);
    }

    /**
     * @param StrategyInterface $strategy
     * @return Token
     */
    public function check(StrategyInterface $strategy) {
      if ($this->valid() === false) {
        return VirtualToken::create();
      }

      $position = $strategy->getNextTokenIndex($this->collection, $this->position);
      if ($position === null) {
        $this->skipCondition = true;
        return VirtualToken::create();
      }


      $this->position = $position;

      $tokenIndex = ($position - 1);
      if (!empty($this->collection[$tokenIndex])) {
        $token = $this->collection[$tokenIndex];;
        $token->setPosition(($position - 1));
      } else {
        $token = VirtualToken::create();
      }

      return $token;
    }

    public function search($string) {
      $section = new Strategy\Search();
      $section->valueIs($string);
      return $this->check($section); 
    }

  }