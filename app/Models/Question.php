<?php
namespace App\Models;

use App\Enums\Version;
use Database\Factories\QuestionFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $perex
 * @property string $description
 * @property Version $version
 * @property Difficulty $difficulty
 * @property Collection<QuestionOption> $options
 * @property QuestionGroup $questionGroup
 * @property DateTimeInterface|null $created_at
 * @property DateTimeInterface|null $updated_at
 */
class Question extends Model
{
    /** @use HasFactory<QuestionFactory> */
    use HasFactory;

    protected $table = 'questions';

    /** @var array<int, string> */
    protected $fillable = [
        'question',
        'difficulty_id',
        'question_group_id',
        'version',
    ];

    /** @var array{
     *     version: int
     * }
     */
    protected $attributes = [
        'version' => Version::One->value,
    ];

    protected $casts = [
        'version' => Version::class,
    ];

    public function difficulty(): BelongsTo
    {
        return $this->belongsTo(Difficulty::class, 'difficulty_id');
    }

    public function questionGroup(): BelongsTo
    {
        return $this->belongsTo(QuestionGroup::class, 'question_group_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class);
    }

    public function getOptions(): Collection
    {
        return $this->difficulty->shuffle_options
            ? $this->options->shuffle()
            : $this->options;
    }

    public function scopeVersion(Builder $query, Version $version): void
    {
        $query->where('version', $version);
    }
}
