<?

  namespace Funivan\PhpTokenizer\BlockExtractor;

  abstract class QueryProcessor implements ExtractorInterface {

    /**
     * @var null|int
     */
    protected $startIndex = null;

    /**
     * @return static
     */
    public static function create() {
      return new static();
    }

    /**
     * @var null|int
     */
    protected $endIndex = null;

    /**
     * @return null|int
     */
    public function getStartIndex() {
      return $this->startIndex;
    }

    /**
     * @return null|int
     */
    public function getEndIndex() {
      return $this->endIndex;
    }

  }