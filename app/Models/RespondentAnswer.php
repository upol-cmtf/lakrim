<?php
namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property Respondent $respondent
 * @property Question $question
 * @property QuestionOption $option
 * @property DateTimeInterface|null $created_at
 * @property DateTimeInterface|null $updated_at
 */
class RespondentAnswer extends Model
{
    protected $table = 'respondents_answers';

    public function respondent(): BelongsTo
    {
        return $this->belongsTo(Respondent::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class);
    }
}
