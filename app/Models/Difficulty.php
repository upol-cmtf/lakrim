<?php
namespace App\Models;

use Database\Factories\DifficultyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $minQuestions
 * @property int $maxQuestions
 * @property string $name
 * @property bool $shuffleQuestions
 */
class Difficulty extends Model
{
    /** @use HasFactory<DifficultyFactory> */
    use HasFactory;

    protected $table = 'difficulty';

    /** @var array<string, int|bool> */
    protected $attributes = [
        'minQuestions' => 1,
        'maxQuestions' => 10,
        'shuffleQuestions' => true,
    ];

    /** @var array<int, string> */
    protected $fillable = [
        'maxQuestions',
        'minQuestions',
        'shuffleQuestions',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
