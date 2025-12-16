<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\ReplacementAppeal;
use App\Observers\ReplacementAppealObserver;
use App\Services\CourseService;
use App\Services\SubjectService;
use App\Services\CourseSubjectService;
use App\Services\CourseSubjectManagementService;
use App\Mediator\TimetableMediator;
use App\Mediator\TimetableMediatorInterface;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

        $this->app->bind(
            TimetableMediatorInterface::class, 
            TimetableMediator::class
        );

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

        // Register unified Course Subject Management Service
        $this->app->singleton('courseSubjectManagement.service', function ($app) {
            return new CourseSubjectManagementService(
                $app->make('course.service'),
                $app->make('subject.service'),
                $app->make('courseSubject.service')
            );
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
