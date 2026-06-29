<?php
namespace App\Models;

use Database\Factories\IslandFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $image
 * @property string|null $guide
 * @property string|null $intro
 * @property array<string, mixed>|null $settings
 * @property Collection<Question> $questions
 * @property Collection<Situation> $situations
 * @property DateTimeInterface|null $created_at
 * @property DateTimeInterface|null $updated_at
 */
class Island extends Model
{
    /** @use HasFactory<IslandFactory> */
    use HasFactory;

    protected $table = 'islands';

    protected $fillable = [
        'name',
        'image',
        'guide',
        'intro',
        'settings',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class);
    }

    public function situations(): HasMany
    {
        return $this->hasMany(Situation::class);
    }
}
