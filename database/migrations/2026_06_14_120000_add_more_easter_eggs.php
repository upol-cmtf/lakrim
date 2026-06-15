<?php

use App\Models\EasterEgg;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        // Kapitánova cesta – v modalu se ukáže obrázek s bludištěm cest k majáku.
        $description = <<<'HTML'
<div class="ee-maze">
    <h3 class="ee-title">Kapitánova cesta</h3>
    <p class="ee-maze__hint">Kapitán se potřebuje bezpečně dostat k majáku. Sledujte jednotlivé cesty očima nebo prstem a zkuste najít tu správnou.</p>
    <p class="ee-maze__hint">Když postupujeme pomalu a sledujeme cestu krok za krokem, snáz se vyhneme slepým uličkám. Stejně pomáhá postupovat i při rozhodování.</p>
    [[image:kapitanova-cesta]]
</div>
<style>
.ee-maze .easter-egg-image { display: block; width: 100%; max-width: 720px; margin: 16px auto 0; height: auto; border-radius: 8px; }
</style>
HTML;

        $evaluation = <<<'HTML'
<div class="ee-maze ee-maze--solution">
    <h3 class="ee-title">Kapitánova cesta</h3>
    <p class="ee-maze__hint">Kapitán se potřebuje bezpečně dostat k majáku. Sledujte jednotlivé cesty očima nebo prstem a zkuste najít tu správnou.</p>
    <p class="ee-maze__hint">Když postupujeme pomalu a sledujeme cestu krok za krokem, snáz se vyhneme slepým uličkám. Stejně pomáhá postupovat i při rozhodování.</p>
    [[image:kapitanova-cesta-spravna-cesta]]
</div>
<style>
.ee-maze--solution .easter-egg-image { display: block; width: 100%; max-width: 720px; margin: 16px auto 0; height: auto; border-radius: 8px; }
</style>
HTML;

        $easterEgg = EasterEgg::create([
            'description' => $description,
            'evaluation' => $evaluation,
        ]);

        $easterEgg->images()->createMany([
            [
                'key' => 'kapitanova-cesta',
                'path' => 'images/easter-eggs/kapitanova-cesta.webp',
                'alt' => 'Kapitánova cesta – bludiště cest k majáku',
                'position' => 0,
            ],
            [
                'key' => 'kapitanova-cesta-spravna-cesta',
                'path' => 'images/easter-eggs/kapitanova-cesta-spravna-cesta.webp',
                'alt' => 'Kapitánova cesta – správná cesta k majáku',
                'position' => 1,
            ],
        ]);

        // Kapitánův vtip – v modalu se ukáže obrázek s vtipem.
        $jokeDescription = <<<'HTML'
<div class="ee-joke">
    <h3 class="ee-title">Kapitánův vtip</h3>
    <p class="ee-joke__hint">Na moři i v životě se někdy hodí na chvíli zpomalit a odlehčit situaci.</p>
    <p class="ee-joke__hint">Krátké pousmání pomáhá uvolnit napětí a znovu se lépe soustředit.</p>
    [[image:kapitanuv-vtip]]
</div>
<style>
.ee-joke .easter-egg-image { display: block; width: 100%; max-width: 720px; margin: 16px auto 0; height: auto; border-radius: 8px; }
</style>
HTML;

        $jokeEgg = EasterEgg::create([
            'description' => $jokeDescription,
            'evaluation' => null,
        ]);

        $jokeEgg->images()->createMany([
            [
                'key' => 'kapitanuv-vtip',
                'path' => 'images/easter-eggs/kapitanuv-vtip.webp',
                'alt' => 'Kapitánův vtip',
                'position' => 0,
            ],
        ]);
    }

    public function down(): void
    {
        EasterEgg::whereHas('images', function ($query) {
            $query->whereIn('key', ['kapitanova-cesta', 'kapitanuv-vtip']);
        })->get()->each->delete();
    }
};
