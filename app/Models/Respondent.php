<?php
namespace App\Models;

use Database\Factories\RespondentFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $token
 * @property string|null $ip
 * @property Age $age
 * @property Difficulty $difficulty
 * @property QuizEvent|null $event
 * @property Collection<RespondentAnswer> $answers
 * @property DateTimeInterface|null $created_at
 * @property DateTimeInterface|null $updated_at
 */
class Respondent extends Model
{
    /** @use HasFactory<RespondentFactory> */
    use HasFactory;

    protected $table = 'respondents';

    /** @var array<int, string> */
    protected $guarded = [];

    public function age(): BelongsTo
    {
        return $this->belongsTo(Age::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(RespondentAnswer::class);
    }

    public function difficulty(): BelongsTo
    {
        return $this->belongsTo(Difficulty::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(QuizEvent::class, 'quiz_event_id');
    }

    /**
     * @return array<int, int>
     */
    public function getAnsweredQuestionIds(): array
    {
        /** @var int[] $questionIds */
        $questionIds = $this->answers->pluck('option.question_id')->toArray();
        return $questionIds;
    }

    public function isAllQuizQuestionsAnswered(): bool
    {
        return $this->answers->count() >= $this->difficulty->max_questions;
    }

    public function getTotalWeight(): float
    {
        return (float) $this->answers()->sum('weight');
    }

    /**
     * @return array{
     *     right: string[],
     *     wrong: string[],
     * }
     */
    public function getFinalSummary(): array
    {
        $summary = [
            'right' => [],
            'wrong' => [],
        ];

        foreach ($this->answers as $answer) {
            $type = $answer->isRightAnswer() ? 'right' : 'wrong';

            if ($answer->option->summary) {
                $summary[$type][] = $answer->option->summary;
            }
        }

        return $summary;
    }
}
