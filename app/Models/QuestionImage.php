<?php
namespace App\Models;

use Database\Factories\QuestionImageFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $question_id
 * @property string $key
 * @property string $path
 * @property string|null $alt
 * @property int $position
 * @property string $url
 * @property Question $question
 * @property DateTimeInterface|null $created_at
 * @property DateTimeInterface|null $updated_at
 */
class QuestionImage extends Model
{
    /** @use HasFactory<QuestionImageFactory> */
    use HasFactory;

    protected $table = 'questions_images';

    protected $fillable = [
        'key',
        'path',
        'alt',
        'position',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    public function getUrlAttribute(): string
    {
        return asset($this->path);
    }

    /**
     * Renders the image as the HTML that replaces its placeholder in a question description.
     */
    public function toHtml(): string
    {
        return sprintf(
            '<img src="%s" alt="%s" class="question-image">',
            e($this->url),
            e($this->alt ?? ''),
        );
    }
}
