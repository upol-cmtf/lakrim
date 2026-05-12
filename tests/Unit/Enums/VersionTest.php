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

    public function testItHasVersionThreeCase(): void
    {
        $this->assertSame(3, Version::Three->value);
    }

    public function testItHasExactlyThreeCases(): void
    {
        $cases = Version::cases();

        $this->assertCount(3, $cases);
    }

    public function testItCanBeCreatedFromIntValue(): void
    {
        $one = Version::from(1);
        $two = Version::from(2);
        $three = Version::from(3);

        $this->assertSame(Version::One, $one);
        $this->assertSame(Version::Two, $two);
        $this->assertSame(Version::Three, $three);
    }

    public function testTryFromReturnsNullForInvalidValue(): void
    {
        $result = Version::tryFrom(99);

        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertNull($result);
    }
}
