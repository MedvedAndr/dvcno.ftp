<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Helpers\AssetsManager;

class AssetsHead extends Component
{
    public function render()
    {
        return view('components.assets.head', [
            'assets' => AssetsManager::get()
        ]);
    }
}