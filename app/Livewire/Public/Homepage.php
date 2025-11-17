<?php

namespace App\Livewire\Public;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Homepage extends Component
{
    #[Title('Home')]
    #[Layout('components.layouts.public')]
    public function render()
    {
        return view('livewire.public.homepage');
    }
}
