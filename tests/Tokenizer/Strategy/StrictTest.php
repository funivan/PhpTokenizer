<?php

declare(strict_types=1);

namespace Test\Funivan\PhpTokenizer\Tokenizer\Strategy;

use Funivan\PhpTokenizer\Collection;
use Funivan\PhpTokenizer\Strategy\Strict;
use PHPUnit\Framework\TestCase;

class StrictTest extends TestCase
{
    public function testSimple(): void
    {
        $code = '<?php echo $a; foreach($users as $user){}';

        $variables = [];
        $collection = Collection::createFromString($code);

        $query = Strict::create()->typeIs(T_VARIABLE);

        foreach ($collection as $index => $token) {
            if ($query->isValid($token)) {
                $variables[] = $token;
            }
        }

        static::assertCount(3, $variables);
    }
}
