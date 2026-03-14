<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Dynamic permission check using route_name only
        Gate::before(function ($user, $ability) {
            // $ability is the string passed to @can('ability')
            // Check if the user has this route_name permission
            if ($user && method_exists($user, 'hasPermission')) {
                return $user->hasPermission($ability) ? true : null;
            }

            return null;
        });
    }
}
