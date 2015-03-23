<?php


  namespace Funivan\PhpTokenizer;

  /**
   * @author Ivan Shcherbak <dev@funivan.com>
   */
  class File {

    /**
     * @var string
     */
    protected $path = null;

    /**
     * @var string
     */
    protected $initialContentHash = null;

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
      $fileTokens = new File($path);
      return $fileTokens;
    }

    /**
     * @param string $path
     */
    public function __construct($path) {
      $this->path = $path;
      $code = file_get_contents($path);
      $this->collection = Collection::initFromString($code);
      
      $this->storeInitialContentHash();
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
      $newCode = $this->collection->assemble();
      return (md5($newCode) != $this->initialContentHash);
    }

    /**
     * @return $this
     */
    protected function storeInitialContentHash() {
      $this->initialContentHash = md5($this->collection);
    }

  }




