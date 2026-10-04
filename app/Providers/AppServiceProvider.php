<?php

namespace App\Providers;

use Anthropic\Client;
use App\Services\Ai\AiAssistant;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AiAssistant::class, function () {
            $key = config('services.anthropic.key');

            return new AiAssistant(
                filled($key) ? new Client(apiKey: $key) : null,
                config('services.anthropic.model'),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['dashboard', 'attendance.index', 'assignments.show-teacher'], function ($view) {
            $view->with('aiEnabled', $this->app->make(AiAssistant::class)->isConfigured());
        });
    }
}
