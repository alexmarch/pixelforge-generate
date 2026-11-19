<?php

namespace App\Providers;

use App\Services\Pixelforge;
use App\Jobs\Pixelforge as JobsPixelforge;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Pixelforge::class, function (Application $app) {
            return new Pixelforge();
        });
        $this->app->bindMethod([JobsPixelforge::class, 'handle'], function (JobsPixelforge $job, Application $app) {
            return $job->handle($app->make(Pixelforge::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        // Gate for admin role allow view report
        Gate::define('view-report', function ($user) {
            return $user->isAdmin();
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
