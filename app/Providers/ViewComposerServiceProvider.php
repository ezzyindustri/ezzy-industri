<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\ProductionProblem;

class ViewComposerServiceProvider extends ServiceProvider
{

    public const HOME = '/manajerial/dashboard'; // Ubah ini untuk default redirect setelah login

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('components.layouts.app', function ($view) {
            $problemCount = 0;
            if (Auth::check() && Auth::user()->role === 'manajerial') {
                $problemCount = ProductionProblem::where('status', 'waiting')->count();
            }
            $view->with('problemCount', $problemCount);
        });
    }
}