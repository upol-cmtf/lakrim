<?php
namespace App\Models;

use Database\Factories\RespondentSituationFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $respondent_id
 * @property int $situation_id
 * @property DateTimeInterface|null $completed_at
 * @property Respondent $respondent
 * @property Situation $situation
 * @property DateTimeInterface|null $created_at
 * @property DateTimeInterface|null $updated_at
 */
class RespondentSituation extends Model
{
    /** @use HasFactory<RespondentSituationFactory> */
    use HasFactory;

    protected $table = 'respondent_situations';

    protected $fillable = [
        'respondent_id',
        'situation_id',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function respondent(): BelongsTo
    {
        return $this->belongsTo(Respondent::class);
    }

    public function situation(): BelongsTo
    {
        return $this->belongsTo(Situation::class);
    }
}
