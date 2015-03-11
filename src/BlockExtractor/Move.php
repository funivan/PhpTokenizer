<?
  namespace Funivan\PhpTokenizer\BlockExtractor;

  class Move extends QueryProcessor {

    const DIRECTION_FORWARD = 1;

    const DIRECTION_BACK = 2;

    /**
     * @var int
     */
    protected $steps = null;

    /**
     * @var int
     */
    protected $direction = null;


    public function __construct($direction, $steps) {
      if (!is_integer($steps)) {
        throw new \InvalidArgumentException("Invalid steps. Expect integer. Given: " . gettype($steps));
      }

      $this->steps = $steps;

      if ($direction !== static::DIRECTION_BACK or $direction !== static::DIRECTION_FORWARD) {
        throw new \InvalidArgumentException("Invalid direction.");
      }

      $this->direction = $direction;
    }

    /**
     * @inheritdoc
     */
    public function process(\Funivan\PhpTokenizer\Collection $collection, $currentIndex) {
      if (static::DIRECTION_FORWARD == $this->direction) {
        $this->endIndex = $currentIndex + $this->steps;
      } elseif (static::DIRECTION_BACK == $this->direction) {
        $this->startIndex = $currentIndex - $this->steps;
      } else {
        throw new \Exception("Can`t process. Invalid direction");
      }
    }

    public function getNextTokenIndexForCheck() {
      return $this->endIndex;
    }

  }