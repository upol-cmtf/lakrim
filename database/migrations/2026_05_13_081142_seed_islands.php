<?php

use App\Models\Island;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        $islands = [
            ['name' => 'Ostrov digitálních pastí', 'image' => 'digital_traps.png'],
            ['name' => 'Ostrov klamavých zpráv', 'image' => 'deceptive_news.png'],
            ['name' => 'Ostrov zneužitých citů', 'image' => 'exploited_emotions.png'],
            ['name' => 'Ostrov falešného bohatství', 'image' => 'fake_wealth.png'],
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
