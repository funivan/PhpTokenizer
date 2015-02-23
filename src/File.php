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
     *  $tokens[0] = '<?';
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
      $this->initialContentHash = md5($code);
      $this->collection = Collection::parseFromString($code);
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
      file_put_contents($this->path, $newCode);
      return true;
    }


    /**
     * Parse current tokens
     *
     * @return $this
     */
    public function refresh() {
      $newCode = $this->collection->assemble();
      $this->collection = Collection::parseFromString($newCode);
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

  }




