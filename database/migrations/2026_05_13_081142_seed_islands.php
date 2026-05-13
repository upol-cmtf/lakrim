<?php

use App\Models\Island;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        $islands = [
            ['name' => 'Ostrov digitálních pastí', 'image' => 'digital_traps.webp'],
            ['name' => 'Ostrov klamavých zpráv', 'image' => 'deceptive_news.webp'],
            ['name' => 'Ostrov zneužitých citů', 'image' => 'exploited_emotions.webp'],
            ['name' => 'Ostrov falešného bohatství', 'image' => 'fake_wealth.webp'],
        ];

        foreach ($islands as $island) {
            Island::create($island);
        }
    }

    public function down(): void
    {
        Island::query()->delete();
    }
};
