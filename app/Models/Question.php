<?php
namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $difficulty
 * @property QuestionGroup $questionGroup
 * @property DateTimeInterface|null $created_at
 * @property DateTimeInterface|null $updated_at
 */
class Question extends Model
{
    protected $table = 'questions';

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class);
    }
}
