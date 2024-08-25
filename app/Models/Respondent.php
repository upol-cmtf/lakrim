<?php
namespace App\Models;

use Database\Factories\RespondentFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $cookie
 * @property string|null $ip
 * @property DateTimeInterface|null $created_at
 * @property DateTimeInterface|null $updated_at
 */
class Respondent extends Model
{
    /** @use HasFactory<RespondentFactory> */
    use HasFactory;

    protected $table = 'respondents';

    public function answers(): HasMany
    {
        return $this->hasMany(RespondentAnswer::class);
    }
}
