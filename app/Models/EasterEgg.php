<?php
namespace App\Models;

use Database\Factories\EasterEggFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $description
 * @property string|null $evaluation
 * @property Collection<EasterEggImage> $images
 * @property DateTimeInterface|null $created_at
 * @property DateTimeInterface|null $updated_at
 */
class EasterEgg extends Model
{
    /** @use HasFactory<EasterEggFactory> */
    use HasFactory;

    protected $table = 'easter_eggs';

    protected $fillable = [
        'description',
        'evaluation',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(EasterEggImage::class)->orderBy('position');
    }

    public function renderedDescription(): string
    {
        return $this->renderImages($this->description);
    }

    public function renderedEvaluation(): ?string
    {
        if ($this->evaluation === null) {
            return null;
        }

        return $this->renderImages($this->evaluation);
    }

    /**
     * Replaces every [[image:key]] placeholder in the given text with the matching
     * image's HTML, so the frontend can render it as-is.
     */
    private function renderImages(string $text): string
    {
        $this->loadMissing('images');
        $images = $this->images->keyBy('key');

        $rendered = preg_replace_callback(
            '/\[\[image:([\w.-]+)\]\]/',
            static function (array $matches) use ($images): string {
                $image = $images->get($matches[1]);

                return $image instanceof EasterEggImage ? $image->toHtml() : '';
            },
            $text,
        );

        return $rendered ?? $text;
    }
}
