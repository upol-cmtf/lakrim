<?php
namespace App\Livewire\Web;

use Livewire\Component;

class RevealingImageItem extends Component
{
    public ?int $position = null;

    public bool $hide = true;

    public function loadQuestion(): void
    {
        $this->hide = false;
        $this->dispatch('answered');
    }
}
