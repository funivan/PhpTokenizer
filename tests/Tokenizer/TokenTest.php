<?php

declare(strict_types=1);

namespace Test\Funivan\PhpTokenizer\Tokenizer;

use Funivan\PhpTokenizer\Exception\InvalidArgumentException;
use Funivan\PhpTokenizer\Token;
use PHPUnit\Framework\TestCase;
use Test\Funivan\PhpTokenizer\FileCreationHelper;

class TokenTest extends TestCase
{
    public function testGetTypeName(): void
    {
        $file = FileCreationHelper::createFileFromCode('<?php echo 1');
        $lastToken = $file->getCollection()->getLast();

        static::assertEquals(token_name(T_LNUMBER), $lastToken->getTypeName());

        unlink($file->getPath());
    }

    public function testSetType(): void
    {
        $file = FileCreationHelper::createFileFromCode('<?php echo 1');
        $lastToken = $file->getCollection()->getLast();

        $lastToken->setType(T_WHITESPACE);
        static::assertEquals(T_WHITESPACE, $lastToken->getType());

        unlink($file->getPath());
    }

    public function testGetData(): void
    {
        $file = FileCreationHelper::createFileFromCode('<?php echo 1');
        $firstToken = $file->getCollection()->getFirst();

        static::assertCount(4, $firstToken->getData());

        unlink($file->getPath());
    }

    public function testToString(): void
    {
        $file = FileCreationHelper::createFileFromCode('<?php echo 1');
        $firstToken = $file->getCollection()->getFirst();

        static::assertEquals('<?php ', (string) $firstToken);

        unlink($file->getPath());
    }

    public function testAssemble(): void
    {
        $token = new Token();
        $token->setValue('123');

        static::assertEquals('123', $token->assemble());
    }

    public function testInvalidTokenType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Token([
            1 => 1,
        ]);
    }

    public function testInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Token([
            0 => 1,
        ]);
    }

    public function testInvalidLine(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Token([1, 'test']);
    }

    public function testAppendToValue(): void
    {
        $token = new Token();
        $token->setValue('123')->appendToValue('test');
        static::assertEquals('123test', $token->getValue());
    }

    public function testTokenData(): void
    {
        $token = new Token([1, 'test', 1, 1]);

        static::assertEquals([1, 'test', 1, 1], $token->getData());
        static::assertEquals(1, $token->getIndex());
        static::assertEquals(1, $token->getLine());
        static::assertEquals(1, $token->getType());
    }

    public function testSetValue(): void
    {
        $token = new Token();
        static::assertNull($token->getValue());
        $token->setValue('a');
        static::assertSame('a', $token->getValue());

        $token->setValue(1);
        static::assertSame('1', $token->getValue());

        $this->expectException(InvalidArgumentException::class);
        $token->setValue(null);
    }

    public function testPrependToTheValue(): void
    {
        $token = new Token();
        static::assertNull($token->getValue());
        $token->setValue('123');
        static::assertSame('123', $token->getValue());

        $token->prependToValue('start');
        static::assertSame('start123', $token->getValue());

        $token->prependToValue(9);
        static::assertSame('9start123', $token->getValue());
        $this->expectException(InvalidArgumentException::class);
        $token->prependToValue(null);
    }

    public function testAppendToTheValue(): void
    {
        $token = new Token();
        $token->setValue('123');
        static::assertSame('123', $token->getValue());

        $token->appendToValue('start');
        static::assertSame('123start', $token->getValue());

        $token->appendToValue(1);
        static::assertSame('123start1', $token->getValue());
        $this->expectException(InvalidArgumentException::class);
        $token->prependToValue(null);
    }

    public function testEqualOnSameTokens(): void
    {
        self::assertTrue(
            (new Token([T_STRING, 'test', 11]))
                ->equal(new Token([T_STRING, 'test', 12]))
        );
    }

    public function testEqualOnDifferentTokensTypes(): void
    {
        self::assertFalse(
            (new Token([T_STRING, 'user_name', 45]))
                ->equal(new Token([T_FUNCTION, 'function', 45]))
        );
    }

    public function testEqualOnDifferentTokensValues(): void
    {
        self::assertFalse(
            (new Token([T_STRING, 'test_string', 10]))
                ->equal(new Token([T_STRING, 'string_test', 10]))
        );
    }
}
