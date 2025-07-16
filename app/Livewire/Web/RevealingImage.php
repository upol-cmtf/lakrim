<?php
namespace App\Livewire\Web;

use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class RevealingImage extends Component
{
    public int $maxTiles = 16;

    public int $rightAnswered = 0;

    public bool $completed = false;

    #[On('answered')]
    public function refreshGrid(): void
    {
        $this->rightAnswered++;
        $this->completed = $this->rightAnswered === $this->maxTiles;
    }

    #[Computed]
    public function solved(): string
    {
        return $this->rightAnswered . '/' . $this->maxTiles;
    }
}
