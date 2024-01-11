<?php

declare(strict_types=1);

namespace Test\Funivan\PhpTokenizer\Demo;

use Funivan\PhpTokenizer\Collection;
use Funivan\PhpTokenizer\QuerySequence\QuerySequence;
use Funivan\PhpTokenizer\Strategy\Possible;
use Funivan\PhpTokenizer\Strategy\Strict;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RemoveEmptyConcatenatedStringsTest extends TestCase
{
    public static function getDemoCode(): array
    {
        return [
            [
                'echo $user."";',
                'echo $user;',
            ],
            [
                'echo $user ."";',
                'echo $user;',
            ],

            [
                'echo $user . "" ;',
                'echo $user;',
            ],

            [
                'echo $user.""          ;',
                'echo $user;',
            ],
            [
                'echo $user.""          ."user";',
                'echo $user."user";',
            ],
            [
                'echo $user.\'\'.$user;',
                'echo $user.$user;',
            ],
            [
                'echo "".$user;',
                'echo $user;',
            ],
            [
                'echo "".$user.""."".$name;',
                'echo $user.$name;',
            ],

            [
                'echo "111".$user.""."".$name;',
                'echo "111".$user.$name;',
            ],

            [
                'echo ""."".$name;',
                'echo $name;',
            ],
        ];
    }

    /**
     * @param string $code
     * @param string $expectCode
     */
    #[DataProvider('getDemoCode')]
    public function testRemoveEmptyString($code, $expectCode): void
    {
        $collection = Collection::createFromString('<?php ' . $code);

        foreach ($collection as $index => $token) {
            $p = new QuerySequence($collection, $index);

            # remove empty string and dot
            $sequence = $p->sequence([
                Strict::create()->valueIs(["''", '""']),
                Possible::create()->typeIs(T_WHITESPACE),
                Strict::create()->valueIs('.'),
                Possible::create()->typeIs(T_WHITESPACE),
            ]);

            if ($p->isValid()) {
                $sequence->remove();
            }

            # remove empty dot and empty string

            $p->setValid(true)->setPosition($index);

            $sequence = $p->sequence([
                Possible::create()->typeIs(T_WHITESPACE),
                Strict::create()->valueIs('.'),
                Possible::create()->typeIs(T_WHITESPACE),
                Strict::create()->valueIs(["''", '""']),
                Possible::create()->typeIs(T_WHITESPACE),
            ]);

            if ($p->isValid()) {
                $sequence->remove();
            }
        }

        $collection[0]->remove();
        $this->assertEquals($expectCode, (string) $collection);
    }
}
