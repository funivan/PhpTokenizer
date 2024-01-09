<?php

declare(strict_types=1);

namespace Test\Funivan\PhpTokenizer\Demo;

use Funivan\PhpTokenizer\Collection;
use Funivan\PhpTokenizer\Pattern\PatternMatcher;
use Funivan\PhpTokenizer\QuerySequence\QuerySequence;
use Funivan\PhpTokenizer\Token;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ExtractVariableFromStringTest extends TestCase
{
    public static function getDemoCode(): array
    {
        return [
            [
                'echo "$user->getName 123 ";',
                'echo "".$user->getName." 123 ";',
            ],
            [
                'echo "$user 123 ";',
                'echo "".$user." 123 ";',
            ],
            [
                'echo "$user";',
                'echo "".$user."";',
            ],
            [
                'echo "$user or $user 123 ";',
                'echo "".$user." or ".$user." 123 ";',
            ],
            [
                'echo "custom $user 123 ";',
                'echo "custom ".$user." 123 ";',
            ],

            [
                'echo "$data";',
                'echo "".$data."";',
            ],

            [
                'echo "$start  ";',
                'echo "".$start."  ";',
            ],
            [
                'echo "custom $end";',
                'echo "custom ".$end."";',
            ],
            [
                'echo "$data custom $end";',
                'echo "".$data." custom ".$end."";',
            ],

        ];
    }

    #[DataProvider('getDemoCode')]
    public function testExtract(string $code, string $expectCode): void
    {
        $collection = Collection::createFromString('<?php ' . $code);
        $checker = new PatternMatcher($collection);
        $checker->apply(function (QuerySequence $q): void {
            $q->strict('"');
            $q->possible(T_ENCAPSED_AND_WHITESPACE);
            $variable = $q->strict(T_VARIABLE);
            $arrow = $q->possible('->');
            $property = new Token();
            if ($arrow->isValid()) {
                $property = $q->strict(T_STRING);
            }
            if ($q->isValid()) {
                $variable->prependToValue('".');
                if ($property->isValid()) {
                    $property->appendToValue('."');
                } else {
                    $variable->appendToValue('."');
                }
                $q->getCollection()->refresh();
            }
        });
        $collection[0]->remove();
        self::assertEquals($expectCode, (string) $collection);
    }
}
