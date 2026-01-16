<?php
namespace Tests\Unit\Enums;

use App\Enums\Version;
use PHPUnit\Framework\TestCase;

class VersionTest extends TestCase
{
    public function testItHasVersionOneCase(): void
    {
        $this->assertSame(1, Version::One->value);
    }

    public function testItHasVersionTwoCase(): void
    {
        $this->assertSame(2, Version::Two->value);
    }

    public function testItHasExactlyTwoCases(): void
    {
        $cases = Version::cases();

        $this->assertCount(2, $cases);
    }

    public function testItCanBeCreatedFromIntValue(): void
    {
        $one = Version::from(1);
        $two = Version::from(2);

        $this->assertSame(Version::One, $one);
        $this->assertSame(Version::Two, $two);
    }

    public function testTryFromReturnsNullForInvalidValue(): void
    {
        $result = Version::tryFrom(99);

        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertNull($result);
    }
}
