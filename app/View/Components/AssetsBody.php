<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Helpers\AssetsManager;

class AssetsBody extends Component
{
    public function render()
    {
        return view('components.assets.body', [
            'assets' => AssetsManager::get()
        ]);
    }
}