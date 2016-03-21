<?php


  namespace Funivan\PhpTokenizer;

  use Funivan\PhpTokenizer\Query\Query;

  /**
   * @author Ivan Shcherbak <dev@funivan.com>
   */
  class File {

    /**
     * @var string
     */
    protected $path = null;

    /**
     * @var Collection
     */
    protected $collection = null;


    /**
     *
     * ```
     * $file = File::open('test.php');
     * $tokens = $file->getCollection();
     * if ($tokens[0]=='<?php'){
     *  $tokens[0] = '<?php';
     *  $file->save();
     * }
     * ```
     *
     * @param string $path
     * @return File
     */
    public static function open($path) {
      return new File($path);
    }


    /**
     * @param string $path
     */
    public function __construct($path) {
      $this->path = $path;
      $code = file_get_contents($path);
      $this->collection = Collection::createFromString($code);
    }


    /**
     * @return Collection
     */
    public function getCollection() {
      return $this->collection;
    }


    /**
     * Save tokens to
     *
     * @return bool
     */
    public function save() {
      if (!$this->isChanged()) {
        return true;
      }
      $newCode = $this->collection->assemble();
      return file_put_contents($this->path, $newCode);
    }


    /**
     * Parse current tokens
     *
     * @return $this
     */
    public function refresh() {
      $newCode = $this->collection->assemble();
      $tokens = Helper::getTokensFromString($newCode);

      $this->collection->setItems($tokens);
      return $this;
    }


    /**
     * @return string
     */
    public function getPath() {
      return $this->path;
    }


    /**
     * @return bool
     */
    public function isChanged() {
      return $this->collection->isChanged();
    }


    /**
     * Alias for Collection::find
     *
     * @param Query $query
     * @return Collection
     */
    public function find(Query $query) {
      return $this->getCollection()->find($query);
    }

  }




