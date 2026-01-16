<?php
namespace Tests\Unit\Enums;

use App\Enums\Sex;
use PHPUnit\Framework\TestCase;

class SexTest extends TestCase
{
    public function testItHasFemaleCase(): void
    {
        $this->assertSame('F', Sex::Female->value);
    }

    public function testItHasMaleCase(): void
    {
        $this->assertSame('M', Sex::Male->value);
    }

    public function testItHasExactlyTwoCases(): void
    {
        $cases = Sex::cases();

        $this->assertCount(2, $cases);
    }

    public function testItCanBeCreatedFromStringValue(): void
    {
        $female = Sex::from('F');
        $male = Sex::from('M');

        $this->assertSame(Sex::Female, $female);
        $this->assertSame(Sex::Male, $male);
    }

    public function testTryFromReturnsNullForInvalidValue(): void
    {
        $result = Sex::tryFrom('X');

        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertNull($result);
    }
}
