<?php
namespace App\Models;

use Database\Factories\QuestionGroupFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property DateTimeInterface|null $created_at
 * @property DateTimeInterface|null $updated_at
 */
class QuestionGroup extends Model
{
    /** @use HasFactory<QuestionGroupFactory> */
    use HasFactory;

    protected $table = 'questions_groups';

    protected $fillable = [
        'name',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
