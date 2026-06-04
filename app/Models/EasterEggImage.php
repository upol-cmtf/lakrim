<?php
namespace App\Models;

use Database\Factories\EasterEggImageFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $easter_egg_id
 * @property string $key
 * @property string $path
 * @property string|null $alt
 * @property int $position
 * @property string $url
 * @property EasterEgg $easterEgg
 * @property DateTimeInterface|null $created_at
 * @property DateTimeInterface|null $updated_at
 */
class EasterEggImage extends Model
{
    /** @use HasFactory<EasterEggImageFactory> */
    use HasFactory;

    protected $table = 'easter_egg_images';

    protected $fillable = [
        'key',
        'path',
        'alt',
        'position',
    ];

    public function easterEgg(): BelongsTo
    {
        return $this->belongsTo(EasterEgg::class, 'easter_egg_id');
    }

    public function getUrlAttribute(): string
    {
        return asset($this->path);
    }

    /**
     * Renders the image as the HTML that replaces its placeholder in a text field.
     */
    public function toHtml(): string
    {
        return sprintf(
            '<img src="%s" alt="%s" class="easter-egg-image">',
            e($this->url),
            e($this->alt ?? ''),
        );
    }
}
