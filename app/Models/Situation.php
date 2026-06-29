<?php
namespace App\Models;

use Database\Factories\SituationFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $island_id
 * @property int $question_id
 * @property int $position
 * @property string|null $title
 * @property string|null $safety_card
 * @property Island $island
 * @property Question $question
 * @property Collection<RespondentSituation> $respondentSituations
 * @property DateTimeInterface|null $created_at
 * @property DateTimeInterface|null $updated_at
 */
class Situation extends Model
{
    /** @use HasFactory<SituationFactory> */
    use HasFactory;

    protected $table = 'situations';

    protected $fillable = [
        'island_id',
        'question_id',
        'position',
        'title',
        'safety_card',
    ];

    public function island(): BelongsTo
    {
        return $this->belongsTo(Island::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function respondentSituations(): HasMany
    {
        return $this->hasMany(RespondentSituation::class);
    }
}
