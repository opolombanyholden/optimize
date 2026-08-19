<?php

namespace App\Http\Controllers;

use App\Models\Vitrine\Atout;
use App\Models\Vitrine\Capture;
use App\Models\Vitrine\Module;
use App\Models\Vitrine\Setting;
use App\Models\Vitrine\SlideHero;

class VitrineController extends Controller
{
    public function home()
    {
        return view('vitrine.home', [
            'slides'   => SlideHero::actif()->orderBy('ordre')->get(),
            'modules'  => Module::actif()->orderBy('ordre')->get(),
            'captures' => Capture::actif()->orderBy('ordre')->get(),
            'atouts'   => Atout::actif()->orderBy('ordre')->get(),
            'cfg'      => Setting::getAll(),
        ]);
    }
}
