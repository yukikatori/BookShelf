<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('*', function ($view) {
            if (auth()->check()) {
                $view->with('unreadNotificationCount', auth()->user()->unreadNotifications->count());
            }
        });
    }
}
