<?php
namespace App\Models;

use App\Enums\Difficulty as DifficultyEnum;
use Database\Factories\DifficultyFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $min_questions
 * @property int $max_questions
 * @property string $name
 * @property bool $shuffle_questions
 * @property bool $shuffle_options
 * @property bool $show_evaluations_for_other_options
 * @property mixed[] $settings
 */
class Difficulty extends Model
{
    /** @use HasFactory<DifficultyFactory> */
    use HasFactory;

    protected $table = 'difficulty';

    public $timestamps = false;

    /** @var array<string, int|bool> */
    protected $attributes = [
        'max_questions' => 10,
        'min_questions' => 1,
        'shuffle_options' => false,
        'shuffle_questions' => false,
    ];

    protected $casts = [
        'settings' => 'array',
        'shuffle_options' => 'bool',
        'shuffle_questions' => 'bool',
        'show_evaluations_for_other_options' => 'bool',
    ];

    /** @var array<int, string> */
    protected $fillable = [
        'max_questions',
        'min_questions',
        'name',
        'shuffle_options',
        'shuffle_questions',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function scopeEasy(Builder $query): void
    {
        $query->where('id', DifficultyEnum::Easy->value);
    }

    public function scopeMedium(Builder $query): void
    {
        $query->where('id', DifficultyEnum::Medium->value);
    }

    public function scopeHard(Builder $query): void
    {
        $query->where('id', DifficultyEnum::Hard->value);
    }
}
