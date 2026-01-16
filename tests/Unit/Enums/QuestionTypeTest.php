<?php
namespace Tests\Unit\Enums;

use App\Enums\QuestionType;
use PHPUnit\Framework\TestCase;

class QuestionTypeTest extends TestCase
{
    public function testItHasSelectCase(): void
    {
        $this->assertSame('select', QuestionType::Select->value);
    }

    public function testItHasMultiSelectCase(): void
    {
        $this->assertSame('multiselect', QuestionType::MultiSelect->value);
    }

    public function testItHasExactlyTwoCases(): void
    {
        $cases = QuestionType::cases();

        $this->assertCount(2, $cases);
    }

    public function testItCanBeCreatedFromStringValue(): void
    {
        $select = QuestionType::from('select');
        $multiSelect = QuestionType::from('multiselect');

        $this->assertSame(QuestionType::Select, $select);
        $this->assertSame(QuestionType::MultiSelect, $multiSelect);
    }

    public function testTryFromReturnsNullForInvalidValue(): void
    {
        $result = QuestionType::tryFrom('invalid');

        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertNull($result);
    }
}
