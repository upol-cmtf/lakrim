<?php
namespace App\Models;

use App\Enums\Difficulty as DifficultyEnum;
use Database\Factories\DifficultyFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $min_questions
 * @property int $max_questions
 * @property string $name
 * @property bool $shuffle_questions
 */
class Difficulty extends Model
{
    /** @use HasFactory<DifficultyFactory> */
    use HasFactory;

    protected $table = 'difficulty';

    public $timestamps = false;

    /** @var array<string, int|bool> */
    protected $attributes = [
        'min_questions' => 1,
        'max_questions' => 10,
        'shuffle_questions' => true,
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
