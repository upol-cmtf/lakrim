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
    <h3 class="ee-title">Hledej rozdíly</h3>
    <p class="ee-diff__hint">My jsme našli 7 rozdílů, kolik jich najdete Vy?</p>
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
    <h3 class="ee-title">Vyhodnocení</h3>
    <p class="ee-diff__hint">A tady jsou všechny rozdíly vyznačené:</p>
    <div class="ee-diff__images">
        [[image:vysledek]]
        [[image:rozdily-2]]
    </div>
</div>
<style>
.ee-diff--result .ee-diff__images { display: flex; flex-wrap: wrap; gap: 16px; justify-content: center; }
.ee-diff--result .easter-egg-image { flex: 1 1 320px; max-width: 100%; height: auto; border-radius: 8px; }
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
    <h3 class="ee-title">Dechové cvičení</h3>
    <p class="ee-breathing__hint">Jsem zlatá rybka. Hromady zlata vám nesplním, ale mám pro vás něco mnohem cennějšího – tři hluboké nádechy pro klidnou mysl.</p>
    <p class="ee-breathing__hint">I ten nejzkušenější kapitán se může v bouři ztratit, když zpanikaří. Stejné je to u telefonu nebo na internetu. Když na vás někdo tlačí, nejlepší obranou je zastavit se a nadechnout. Tím vezmete kormidlo zpět do svých rukou. Pojďme si to vyzkoušet.</p>
    <video class="ee-breathing__video" controls playsinline preload="metadata">
        <source src="/videos/easter-eggs/sasanka-dechove-cviceni.mp4" type="video/mp4">
        Váš prohlížeč neumí přehrát video.
    </video>
</div>
<style>
.ee-breathing__video { display: block; width: 100%; max-width: 720px; margin: 0 auto; height: auto; border-radius: 8px; }
</style>
HTML;

        $breathingEvaluation = <<<'HTML'
<div class="ee-breathing ee-breathing--result">
    <h3 class="ee-title">Vyhodnocení</h3>
    <p class="ee-breathing__hint">Výborně. Teď máte čistou hlavu a pevnou ruku na kormidle. Kdykoliv v životě ucítíte tlak a nejistotu, vzpomeňte si na mě a prostě se na chvíli zastavte a v klidu nadechněte. Šťastnou plavbu.</p>
</div>
HTML;

        EasterEgg::create([
            'description' => $breathingDescription,
            'evaluation' => $breathingEvaluation,
        ]);

        // Cvičení s rybkou – v modalu se přehraje video vedoucí cvičení.
        $fishDescription = <<<'HTML'
<div class="ee-exercise">
    <h3 class="ee-title">Cvičení s rybkou</h3>
    <p class="ee-exercise__hint">Tady v moři se hýbe úplně všechno, ale u obrazovky člověk ztuhne jako zkamenělý útes. Když se tělo nehýbe, i mozek pak pomaleji přemýšlí. Pojďme si na minutku protáhnout, ať máte čistou hlavu na další úkoly. Zůstaňte klidně sedět a zkuste to se mnou:</p>
    <video class="ee-exercise__video" controls loop playsinline preload="metadata">
        <source src="/videos/easter-eggs/cviceni-s-rybkou.mp4" type="video/mp4">
        Váš prohlížeč neumí přehrát video.
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
