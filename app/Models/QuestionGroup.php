<?php
namespace App\Models;

use DateTimeInterface;
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
    protected $table = 'questions_groups';

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
