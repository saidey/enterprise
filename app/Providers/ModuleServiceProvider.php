<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $modules = config('modules', []);

        foreach ($modules as $code => $meta) {
            if (! ($meta['enabled'] ?? false)) {
                continue;
            }

            $provider = $meta['service_provider'] ?? null;

            if ($provider && class_exists($provider)) {
                $this->app->register($provider);
            }
        }
    }
}
