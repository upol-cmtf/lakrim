<?php
namespace App\Models;

use Database\Factories\QuestionOptionFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property float $weight
 * @property boolean $right
 * @property string $evaluation_title
 * @property string $evaluation
 * @property string $summary
 * @property int $question_id
 * @property Question $question
 * @property DateTimeInterface|null $created_at
 * @property DateTimeInterface|null $updated_at
 */
class QuestionOption extends Model
{
    /** @use HasFactory<QuestionOptionFactory> */
    use HasFactory;

    protected $table = 'questions_options';

    protected $fillable = [
        'name',
        'description',
        'weight',
        'evaluation',
        'evaluation_title',
    ];

    protected $casts = [
        'right' => 'boolean',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id');
    }
}
