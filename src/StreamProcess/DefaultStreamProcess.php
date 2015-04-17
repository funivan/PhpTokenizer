<?php
  namespace Funivan\PhpTokenizer\StreamProcess;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Exception\InvalidArgumentException;
  use Funivan\PhpTokenizer\Query\QueryInterface;
  use Funivan\PhpTokenizer\Strategy\Move;
  use Funivan\PhpTokenizer\Strategy\Possible;
  use Funivan\PhpTokenizer\Strategy\Search;
  use Funivan\PhpTokenizer\Strategy\StrategyInterface;
  use Funivan\PhpTokenizer\Strategy\Strict;
  use Funivan\PhpTokenizer\Token;

  /**
   * Start from specific position and check token from this position according to strategies
   */
  class DefaultStreamProcess {

    /**
     * @var
     */
    private $position;

    /**
     * @var Collection
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
    public function __construct(Collection $collection, $position, $skipWhitespaces = false) {
      $this->position = $position;
      $this->collection = $collection;
      $this->skipWhitespaces = $skipWhitespaces;
    }


    /**
     * Strict validation of condition
     * 
     * @param int|string $condition
     * @return Token
     */
    public function strict($condition) {
      $query = $this->buildQuery($condition, Strict::create());
      return $this->process($query);
    }

    /**
     * Check if token possible valid for our condition
     * 
     * @param int|string $condition
     * @return Token
     */
    public function possible($condition) {
      $query = $this->buildQuery($condition, Possible::create());
      return $this->process($query);
    }

    /**
     * @param string $start
     * @param string $end
     * @return Token
     */
    public function section($start, $end) {
      $section = new \Funivan\PhpTokenizer\Strategy\Section();
      $section->setDelimiters($start, $end);
      return $this->process($section);
    }

    /**
     * By default we search forward
     * 
     * @param int|string $condition
     * @param null $direction
     * @return Token
     */
    public function search($condition, $direction = null) {
      $strategy = Search::create();
      if ($direction !== null) {
        $strategy->setDirection($direction);
      }
      $query = $this->buildQuery($condition, $strategy);
      return $this->process($query);
    }

    /**
     * @param int $steps
     * @return Token
     */
    public function move($steps) {
      return $this->process(Move::create($steps));
    }

    /**
     * @param array $conditions
     * @return Collection
     */
    public function sequence(array $conditions) {
      $range = new Collection();
      foreach ($conditions as $value) {
        $range[] = $this->check($value);
      }

      return $range;
    }

    /**
     * @param string|int|$value
     * @return Token
     */
    private function check($value) {
      if ($value instanceof StrategyInterface) {
        $query = $value;
      } else {
        $query = $this->buildQuery($value, Strict::create());
      }

      $token = $this->process($query);
      return $token;
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
    public function process(StrategyInterface $strategy) {

      if ($this->isValid() === false) {
        return new Token();
      }

      $result = $strategy->process($this->collection, $this->position);

      if ($result->isValid() == false) {
        $this->valid = false;
        return new Token();
      }

      $this->position = $result->getNexTokenIndex();

      $token = $result->getToken();
      if ($token === null) {
        $token = new Token();
      }

      if ($this->skipWhitespaces and isset($this->collection[$this->position]) and $this->collection[$this->position]->getType() === T_WHITESPACE) {
        # skip whitespaces in next check
        $this->position++;
      }

      return $token;
    }

    /**
     * @todo change queryInterface
     *
     * @param StrategyInterface|string|int $value
     * @param QueryInterface $defaultStrategy
     * @return StrategyInterface
     */
    private function buildQuery($value, QueryInterface $defaultStrategy) {
      if (is_string($value) or $value === null) {
        $query = $defaultStrategy;
        $query->valueIs($value);
      } elseif (is_int($value)) {
        $query = $defaultStrategy;
        $query->typeIs($value);
      } else {
        throw new InvalidArgumentException("Invalid token condition. Expect string or int or StrategyInterface");
      }

      return $query;
    }

  }