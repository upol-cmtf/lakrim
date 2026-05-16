<?php
namespace Tests\Feature\Web\Quiz;

use App\Enums\Difficulty;
use App\Enums\Version;
use App\Models\Question;
use App\Models\QuestionImage;
use App\Models\QuestionOption;
use App\Models\Respondent;
use Tests\TestCase;

class QuestionImageTest extends TestCase
{
    private const ROUTE_NAME = 'web.quiz.question';

    public function testImageBelongsToQuestionThroughImagesRelation(): void
    {
        $question = Question::factory()->create([
            'difficulty_id' => Difficulty::Easy->value,
        ]);

        $image = QuestionImage::factory()->for($question)->create();

        $this->assertTrue($question->images->contains($image));
        $this->assertTrue($image->question->is($question));
    }

    public function testImagesRelationIsOrderedByPosition(): void
    {
        $question = Question::factory()->create([
            'difficulty_id' => Difficulty::Easy->value,
        ]);

        $second = QuestionImage::factory()->for($question)->create(['position' => 2]);
        $first = QuestionImage::factory()->for($question)->create(['position' => 1]);

        $this->assertSame(
            [$first->id, $second->id],
            $question->images()->get()->pluck('id')->all(),
        );
    }

    public function testUrlAccessorBuildsAnAssetUrlFromThePath(): void
    {
        $image = QuestionImage::factory()->make(['path' => 'images/questions/sample.png']);

        $this->assertSame(asset('images/questions/sample.png'), $image->url);
    }

    public function testToHtmlRendersAnImageTag(): void
    {
        $image = QuestionImage::factory()->make([
            'path' => 'images/questions/sample.png',
            'alt' => 'Popis obrázku',
        ]);

        $this->assertSame(
            sprintf(
                '<img src="%s" alt="Popis obrázku" class="question-image">',
                e(asset('images/questions/sample.png')),
            ),
            $image->toHtml(),
        );
    }

    public function testRenderedDescriptionReplacesThePlaceholderWithImageHtml(): void
    {
        $question = Question::factory()->create([
            'difficulty_id' => Difficulty::Easy->value,
            'description' => 'Před [[image:hero]] po.',
        ]);

        $image = QuestionImage::factory()->for($question)->create(['key' => 'hero']);

        $this->assertSame(
            'Před ' . $image->toHtml() . ' po.',
            $question->renderedDescription(),
        );
    }

    public function testRenderedDescriptionReplacesEveryPlaceholderWithItsOwnImage(): void
    {
        $question = Question::factory()->create([
            'difficulty_id' => Difficulty::Easy->value,
            'description' => '[[image:first]] a [[image:second]]',
        ]);

        $first = QuestionImage::factory()->for($question)->create(['key' => 'first']);
        $second = QuestionImage::factory()->for($question)->create(['key' => 'second']);

        $this->assertSame(
            $first->toHtml() . ' a ' . $second->toHtml(),
            $question->renderedDescription(),
        );
    }

    public function testRenderedDescriptionDropsPlaceholdersWithoutAMatchingImage(): void
    {
        $question = Question::factory()->create([
            'difficulty_id' => Difficulty::Easy->value,
            'description' => 'Text [[image:missing]] konec.',
        ]);

        $this->assertSame('Text  konec.', $question->renderedDescription());
    }

    public function testRenderedDescriptionLeavesDescriptionWithoutPlaceholdersUntouched(): void
    {
        $question = Question::factory()->create([
            'difficulty_id' => Difficulty::Easy->value,
            'description' => 'Otázka bez obrázku.',
        ]);

        $this->assertSame('Otázka bez obrázku.', $question->renderedDescription());
    }

    public function testWithImagesFactoryStateCreatesImages(): void
    {
        $question = Question::factory()->withImages(3)->create([
            'difficulty_id' => Difficulty::Easy->value,
        ]);

        $this->assertCount(3, $question->images);
    }

    public function testQuestionEndpointReturnsTheRenderedDescription(): void
    {
        $question = Question::factory()
            ->has(QuestionOption::factory()->count(4), 'options')
            ->create([
                'difficulty_id' => Difficulty::Easy->value,
                'description' => 'Pozor na [[image:phishing]].',
            ]);

        $image = QuestionImage::factory()->for($question)->create(['key' => 'phishing']);

        $respondent = Respondent::factory()->createOneQuietly([
            'version' => Version::One->value,
        ]);

        $this->postJson(route(self::ROUTE_NAME), [
            'respondent_token' => $respondent->token,
        ])
            ->assertOk()
            ->assertJsonPath('data.description', 'Pozor na ' . $image->toHtml() . '.');
    }
}
