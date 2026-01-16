<?php
namespace Tests\Unit\Enums;

use App\Enums\Difficulty;
use PHPUnit\Framework\TestCase;

class DifficultyTest extends TestCase
{
    public function testItHasEasyCase(): void
    {
        $this->assertSame(1, Difficulty::Easy->value);
    }

    public function testItHasMediumCase(): void
    {
        $this->assertSame(2, Difficulty::Medium->value);
    }

    public function testItHasHardCase(): void
    {
        $this->assertSame(3, Difficulty::Hard->value);
    }

    public function testItHasExactlyThreeCases(): void
    {
        $cases = Difficulty::cases();

        $this->assertCount(3, $cases);
    }

    public function testItCanBeCreatedFromIntValue(): void
    {
        $easy = Difficulty::from(1);
        $medium = Difficulty::from(2);
        $hard = Difficulty::from(3);

        $this->assertSame(Difficulty::Easy, $easy);
        $this->assertSame(Difficulty::Medium, $medium);
        $this->assertSame(Difficulty::Hard, $hard);
    }

    public function testTryFromReturnsNullForInvalidValue(): void
    {
        $result = Difficulty::tryFrom(99);

        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertNull($result);
    }
}
