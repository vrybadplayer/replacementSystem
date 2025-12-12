<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\ReplacementAppeal;
use App\Observers\ReplacementAppealObserver;
use App\Services\CourseService;
use App\Services\SubjectService;
use App\Services\CourseSubjectService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Course Service
        $this->app->singleton('course.service', function ($app) {
            return new CourseService();
        });

        // Register Subject Service
        $this->app->singleton('subject.service', function ($app) {
            return new SubjectService();
        });

        // Register CourseSubject Service
        $this->app->singleton('courseSubject.service', function ($app) {
            return new CourseSubjectService();
        });
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
