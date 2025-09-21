<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Question extends Component
{

    public string $country;
    public int $currentQuestion;
    public int $totalQuestions;

    /**
     * Create a new component instance.
     */
    public function __construct($country, $currentQuestion, $totalQuestions)
    {
        $this->country = $country;
        $this->currentQuestion = $currentQuestion;
        $this->totalQuestions = $totalQuestions;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.question');
    }
}
