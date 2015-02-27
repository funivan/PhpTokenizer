<?

  namespace Funivan\PhpTokenizer\BlockExtractor;

  abstract class ExtractProcessor implements ExtractorInterface {

    /**
     * @var null|int
     */
    protected $startIndex = null;

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