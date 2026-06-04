<?php
namespace App\Models;

use Database\Factories\RespondentEasterEggFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $respondent_id
 * @property int $easter_egg_id
 * @property int $seconds
 * @property DateTimeInterface|null $completed_at
 * @property Respondent $respondent
 * @property EasterEgg $easterEgg
 * @property DateTimeInterface|null $created_at
 * @property DateTimeInterface|null $updated_at
 */
class RespondentEasterEgg extends Model
{
    /** @use HasFactory<RespondentEasterEggFactory> */
    use HasFactory;

    protected $table = 'respondent_easter_eggs';

    protected $attributes = [
        'seconds' => 0,
    ];

    protected $fillable = [
        'completed_at',
        'easter_egg_id',
        'respondent_id',
        'seconds',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function respondent(): BelongsTo
    {
        return $this->belongsTo(Respondent::class);
    }

    public function easterEgg(): BelongsTo
    {
        return $this->belongsTo(EasterEgg::class);
    }
}
