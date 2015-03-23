<?

  namespace Funivan\PhpTokenizer\Extractor;

  /**
   *
   * @package Funivan\PhpTokenizer\Extractor
   */
  class ExtractorResult {

    /**
     * @var int
     */
    protected $start;

    /**
     * @var int
     */
    protected $end;

    /**
     * @param $start
     * @param $end
     */
    public function __construct($start, $end) {
      if (!is_integer($start)) {
        throw new \Funivan\PhpTokenizer\Exception\InvalidArgumentException("Expect start ast int type. Given:" . gettype($start));
      }

      if (!is_integer($end)) {
        throw new \Funivan\PhpTokenizer\Exception\InvalidArgumentException("Expect end ast int type. Given:" . gettype($end));
      }

      if ($start > $end) {
        throw new \InvalidArgumentException("Start index must be < than end index");
      }

      $this->start = $start;
      $this->end = $end;
    }

    /**
     * @return int
     */
    public function getStart() {
      return $this->start;
    }

    /**
     * @return int
     */
    public function getEnd() {
      return $this->end;
    }

    /**
     * @return int
     */
    public function getLength() {
      return ($this->end - $this->start + 1);
    }
    
  }