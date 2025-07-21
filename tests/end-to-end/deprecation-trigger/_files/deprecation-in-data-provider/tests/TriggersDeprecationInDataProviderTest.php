<?php

namespace PHPUnit\TestFixture\DeprecationInDataProvider;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TriggersDeprecationInDataProviderTest extends TestCase
{
    #[Test]
    public function method1(): void
    {
        self::assertTrue(true);
    }

    #[Test]
    #[DataProvider('dataProvider')]
    public function method2(bool $value): void
    {
        self::assertTrue($value);
    }

    public static function dataProvider(): iterable
    {
        @trigger_error('some deprecation', \E_USER_DEPRECATED);

        yield [true];
    }

    #[Test]
    public function method3(): void
    {
        self::assertTrue(true);
    }
}
