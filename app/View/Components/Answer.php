<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Answer extends Component
{
    public string $capital;
    
    /**
     * Create a new component instance.
     */
    public function __construct($capital)
    {
        $this->capital = $capital;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.answer');
    }
}
