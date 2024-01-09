<?php

declare(strict_types=1);

namespace Test\Funivan\PhpTokenizer;

use PHPUnit\Framework\TestCase;

class DocumentationTestCase extends TestCase
{
    final public const DIR = __DIR__ . '/..';

    final public const DOCS_DIR = self::DIR . '/doc';

    final public const BUILD_DIR = self::DIR . '/build';

    public function getDocumentationDataProvider(): array
    {
        $files = glob(self::DOCS_DIR . '/**');
        $files[] = self::DIR . '/README.md';
        $data = [];
        foreach ($files as $file) {
            $data[] = [$file];
        }
        return $data;
    }

    /**
     * @dataProvider getDocumentationDataProvider
     */
    public function testDocumentation(string $docFilePath): void
    {
        $i = 0;
        $data = file_get_contents($docFilePath);
        preg_match_all('!```php(.*)```!sU', $data, $fileCodeChunks);
        $autoloadPart = sprintf('<?php require "%s";', self::BUILD_DIR . '/../vendor/autoload.php');
        $errorHandler = 'set_error_handler(function($number, $message){echo $message;die(1);});';
        foreach ((array) $fileCodeChunks[1] as $code) {
            $filePath = self::BUILD_DIR . '/' . $i . '.php';
            file_put_contents($filePath, $autoloadPart . "\n" . $errorHandler . "\n" . $code);
            $i++;
            @exec('php ' . $filePath . ' 2>/dev/null', $result, $exitCode);
            @unlink($filePath);
            if ($exitCode >= 1) {
                self::fail(
                    'Documentation file ' . realpath($docFilePath) . ' contains errors in code sample block #' . $i
                    . "\n"
                    . implode("\n", $result)
                );
            }
        }
    }
}
