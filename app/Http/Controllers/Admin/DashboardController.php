<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pack;
use App\Models\Service;
use App\Models\Builder;
use App\Models\Hero;
use App\Models\Slide;
use App\Models\User;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('admin.dashboard', [
            'packsCount'    => Pack::count(),
            'servicesCount' => Service::count(),
            'buildersCount' => Builder::count(),
            'heroesCount'   => Hero::count(),
            'slidesCount'   => Slide::count(),
            'usersCount'    => User::count(),
        ]);
    }
}
