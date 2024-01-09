<?php

declare(strict_types=1);


namespace Test\Funivan\PhpTokenizer;

use Funivan\PhpTokenizer\File;

class FileCreationHelper
{

    /**
     * @return File
     */
    public static function createFileFromCode(string $code): File
    {
        $path = tempnam('/tmp', 'testFileOther');
        file_put_contents($path, $code);

        return new File($path);
    }
}
