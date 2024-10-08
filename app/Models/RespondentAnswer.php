<?php
namespace App\Models;

use Database\Factories\RespondentAnswerFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $seconds
 * @property float $weight
 * @property Respondent $respondent
 * @property QuestionOption $option
 * @property DateTimeInterface|null $created_at
 * @property DateTimeInterface|null $updated_at
 */
class RespondentAnswer extends Model
{
    /** @use HasFactory<RespondentAnswerFactory> */
    use HasFactory;

    protected $table = 'respondents_answers';

    /** @var array<string, mixed> */
    protected $attributes = [
        'seconds' => 0,
        'weight' => 0,
    ];

    /** @var array<int, string> */
    protected $fillable = [
        'seconds',
        'weight',
        'respondent_id',
        'question_option_id',
    ];

    public function respondent(): BelongsTo
    {
        return $this->belongsTo(Respondent::class);
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class);
    }
}
