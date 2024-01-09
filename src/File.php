<?php

declare(strict_types=1);

namespace Funivan\PhpTokenizer;

use Funivan\PhpTokenizer\Query\Query;

class File
{

    protected Collection $collection;


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
     * @return File
     */
    public static function open(string $path): File
    {
        return new File($path);
    }


    public function __construct(protected string $path)
    {
        $code = file_get_contents($this->path);
        $this->collection = Collection::createFromString($code);
    }


    /**
     * @return Collection
     */
    public function getCollection(): Collection
    {
        return $this->collection;
    }


    /**
     * Save tokens to
     *
     * @return bool|int
     */
    public function save()
    {
        if (!$this->isChanged()) {
            return true;
        }
        $newCode = $this->collection->assemble();
        return file_put_contents($this->path, $newCode);
    }


    /**
     * Parse current tokens
     *
     * @return self
     */
    public function refresh(): self
    {
        $newCode = $this->collection->assemble();
        $tokens = Helper::getTokensFromString($newCode);

        $this->collection->setItems($tokens);
        return $this;
    }


    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }


    /**
     * @return bool
     */
    public function isChanged(): bool
    {
        return $this->collection->isChanged();
    }


    /**
     * Alias for Collection::find
     *
     * @return Collection
     */
    public function find(Query $query): Collection
    {
        return $this->getCollection()->find($query);
    }

}




