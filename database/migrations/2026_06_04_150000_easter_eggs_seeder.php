<?php

use App\Models\EasterEgg;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        // Hledání rozdílů – v modalu se ukážou dva obrázky vedle sebe,
        // ve vyhodnocení pak jeden obrázek s vyznačenými rozdíly.
        $description = <<<'HTML'
<div class="ee-diff">
    <p class="ee-diff__hint">Starý strážce majáku má ve svém pokoji nepořádek a něco se změnilo. Najdeš všechny rozdíly mezi obrázky?</p>
    <div class="ee-diff__images">
        [[image:rozdily-1]]
        [[image:rozdily-2]]
    </div>
</div>
<style>
.ee-diff__images { display: flex; flex-wrap: wrap; gap: 16px; justify-content: center; }
.ee-diff__images .easter-egg-image { flex: 1 1 320px; max-width: 100%; height: auto; border-radius: 8px; }
</style>
HTML;

        $evaluation = <<<'HTML'
<div class="ee-diff ee-diff--result">
    <p class="ee-diff__hint">A tady jsou všechny rozdíly vyznačené:</p>
    <div class="ee-diff__images">
        [[image:vysledek]]
    </div>
</div>
<style>
.ee-diff--result .ee-diff__images { justify-content: center; }
.ee-diff--result .easter-egg-image { max-width: 100%; height: auto; border-radius: 8px; }
</style>
HTML;

        $easterEgg = EasterEgg::create([
            'description' => $description,
            'evaluation' => $evaluation,
        ]);

        $easterEgg->images()->createMany([
            [
                'key' => 'rozdily-1',
                'path' => 'images/easter-eggs/rozdily-1.webp',
                'alt' => 'Pokoj strážce majáku – první obrázek',
                'position' => 0,
            ],
            [
                'key' => 'rozdily-2',
                'path' => 'images/easter-eggs/rozdily-2.webp',
                'alt' => 'Pokoj strážce majáku – druhý obrázek',
                'position' => 1,
            ],
            [
                'key' => 'vysledek',
                'path' => 'images/easter-eggs/rozdily-vysledek.webp',
                'alt' => 'Pokoj strážce majáku – vyznačené rozdíly',
                'position' => 2,
            ],
        ]);

        // Dechové cvičení – v modalu se přehraje video, které vede dýchání.
        $breathingDescription = <<<'HTML'
<div class="ee-breathing">
    <p class="ee-breathing__hint">Zastav se na chvíli a nadechni se spolu se sasankou. Sleduj video a dýchej v jejím rytmu.</p>
    <video class="ee-breathing__video" controls loop playsinline preload="metadata">
        <source src="/videos/easter-eggs/sasanka-dechove-cviceni.mp4" type="video/mp4">
        Tvůj prohlížeč neumí přehrát video.
    </video>
</div>
<style>
.ee-breathing__video { display: block; width: 100%; max-width: 720px; margin: 0 auto; height: auto; border-radius: 8px; }
</style>
HTML;

        // U dechového cvičení se nic nevyhodnocuje – evaluation zůstává null.
        EasterEgg::create([
            'description' => $breathingDescription,
            'evaluation' => null,
        ]);

        // Cvičení s rybkou – v modalu se přehraje video vedoucí cvičení.
        $fishDescription = <<<'HTML'
<div class="ee-exercise">
    <p class="ee-exercise__hint">Protáhni se a zacvič si spolu s rybkou. Pusť si video a opakuj pohyby.</p>
    <video class="ee-exercise__video" controls loop playsinline preload="metadata">
        <source src="/videos/easter-eggs/cviceni-s-rybkou.mp4" type="video/mp4">
        Tvůj prohlížeč neumí přehrát video.
    </video>
</div>
<style>
.ee-exercise__video { display: block; width: 100%; max-width: 720px; margin: 0 auto; height: auto; border-radius: 8px; }
</style>
HTML;

        EasterEgg::create([
            'description' => $fishDescription,
            'evaluation' => null,
        ]);
    }

    public function down(): void
    {
        EasterEgg::all()->each->delete();
    }
};
