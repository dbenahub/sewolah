<?php

namespace App\Livewire\Public;

use Livewire\Component;

class LanguageSwitcher extends Component
{
    public function switchTo(string $locale)
    {
        if (in_array($locale, ['ms', 'en'])) {
            session(['locale' => $locale]);
            $this->redirect(request()->header('Referer') ?? route('landing'), navigate: false);
        }
    }

    public function render()
    {
        return view('livewire.public.language-switcher');
    }
}
