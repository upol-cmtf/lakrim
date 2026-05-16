<?php
namespace App\Models;

use App\Enums\QuestionType;
use App\Enums\Version;
use Database\Factories\QuestionFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $perex
 * @property string $description
 * @property Version $version
 * @property Difficulty $difficulty
 * @property QuestionType $type
 * @property Collection<QuestionOption> $options
 * @property Collection<QuestionImage> $images
 * @property QuestionGroup $questionGroup
 * @property Collection<Island> $islands
 * @property DateTimeInterface|null $created_at
 * @property DateTimeInterface|null $updated_at
 * @property mixed[]|null $settings
 * @property string $first_wrong_answer_evaluation
 * @property string $second_wrong_answer_evaluation
 */
class Question extends Model
{
    /** @use HasFactory<QuestionFactory> */
    use HasFactory;

    protected $table = 'questions';

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
        'type' => QuestionType::Select->value,
        'version' => Version::One->value,
    ];

    protected $casts = [
        'settings' => 'array',
        'type' => QuestionType::class,
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

    public function islands(): BelongsToMany
    {
        return $this->belongsToMany(Island::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(QuestionImage::class)->orderBy('position');
    }

    /**
     * Returns the description with every [[image:key]] placeholder replaced by the
     * matching image's HTML, so the frontend can render it as-is.
     */
    public function renderedDescription(): string
    {
        $this->loadMissing('images');
        $images = $this->images->keyBy('key');

        $rendered = preg_replace_callback(
            '/\[\[image:([\w.-]+)\]\]/',
            static function (array $matches) use ($images): string {
                $image = $images->get($matches[1]);

                return $image instanceof QuestionImage ? $image->toHtml() : '';
            },
            $this->description,
        );

        return $rendered ?? $this->description;
    }

    public function getOptions(): Collection
    {
        return $this->difficulty->shuffle_options
            ? $this->options->shuffle()
            : $this->options;
    }

    public function isTypeSelect(): bool
    {
        return $this->type === QuestionType::Select;
    }

    public function isTypeMultipleSelect(): bool
    {
        return $this->type === QuestionType::MultiSelect;
    }

    public function scopeVersion(Builder $query, Version $version): Builder
    {
        $query->where('version', $version);

        return $query;
    }
}
