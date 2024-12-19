<?php
namespace App\Models;

use Carbon\Carbon;
use Database\Factories\QuizEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $hash
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class QuizEvent extends Model
{
    /** @use HasFactory<QuizEventFactory> */
    use HasFactory;

    protected $table = 'quiz_events';

    protected $fillable = [
        'name',
        'hash',
    ];

    public function respondents(): HasMany
    {
        return $this->hasMany(Respondent::class);
    }
}
