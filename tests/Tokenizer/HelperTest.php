<?php

declare(strict_types=1);

namespace Test\Funivan\PhpTokenizer\Tokenizer;

use Funivan\PhpTokenizer\Collection;
use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{
    /**
     * @void
     */
    public function testCheckLines()
    {
        $code = '<?php return [
      ];';

        $collection = Collection::createFromString($code);
        $this->assertEquals(2, $collection->getLast()->getLine());

        $code = '<?php 
      
      return [
      ];';

        $collection = Collection::createFromString($code);
        $this->assertEquals(4, $collection->getLast()->getLine());
    }
}
