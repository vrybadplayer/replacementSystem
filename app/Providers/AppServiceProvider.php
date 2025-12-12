<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\ReplacementAppeal;
use App\Observers\ReplacementAppealObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register the Observer for ReplacementAppeal
        ReplacementAppeal::observe(ReplacementAppealObserver::class);
    }
}
