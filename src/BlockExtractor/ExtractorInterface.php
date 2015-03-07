<?

  namespace Funivan\PhpTokenizer\BlockExtractor;

  use Funivan\PhpTokenizer\Collection;

  interface ExtractorInterface {

    public function process(Collection $collection, $currentIndex);

    public function getStartIndex();

    public function getEndIndex();

    public function getNextTokenIndexForCheck();

  }