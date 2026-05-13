<?php
namespace App\Models;

use App\Enums\Version;
use Database\Factories\RespondentFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $token
 * @property string|null $ip
 * @property string|null $student_id
 * @property string|null $sex
 * @property boolean $finished
 * @property Version $version
 * @property Age|null $age
 * @property QuizEvent|null $event
 * @property Collection<RespondentAnswer> $answers
 * @property Collection<RespondentSituation> $situations
 * @property DateTimeInterface|null $created_at
 * @property DateTimeInterface|null $updated_at
 * @property int|null $cnt
 */
class Respondent extends Model
{
    /** @use HasFactory<RespondentFactory> */
    use HasFactory;

    protected $table = 'respondents';

    /** @var array<int, string> */
    protected $guarded = [];

    /** @var array{
     *     version: int
     * }
     */
    protected $attributes = [
        'version' => Version::One->value,
    ];

    protected $casts = [
        'finished' => 'boolean',
        'version' => Version::class,
    ];

    public function age(): BelongsTo
    {
        return $this->belongsTo(Age::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(RespondentAnswer::class);
    }

    public function situations(): HasMany
    {
        return $this->hasMany(RespondentSituation::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(QuizEvent::class, 'quiz_event_id');
    }

    /** @return int[] */
    public function getAnsweredQuestionIds(): array
    {
        /** @var int[] $questionIds */
        $questionIds = $this->answers->pluck('option.question_id')->unique()->toArray();
        return $questionIds;
    }

    public function isAllQuizQuestionsAnswered(): bool
    {
        $difficulty = Difficulty::find($this->version->value);
        assert($difficulty instanceof Difficulty);

        return count($this->getAnsweredQuestionIds()) >= $difficulty->max_questions;
    }

    public function getTotalWeight(): float
    {
        return (float) $this->answers()->sum('weight');
    }

    public function scopeFinished(Builder $query): void
    {
        $query->where('finished', true);
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
            if ($answer->attempt > 1) {
                continue;
            }

            $type = $answer->isRightAnswer() ? 'right' : 'wrong';

            if ($answer->option->summary) {
                $summary[$type][$answer->option->question->id] = $answer->option->summary;
            }
        }

        return $summary;
    }
}
