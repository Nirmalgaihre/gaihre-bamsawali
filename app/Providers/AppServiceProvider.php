<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        View::composer(['layouts.public', 'layouts.admin', 'admin.settings', 'auth.login', 'public.page', 'public.home'], function (\Illuminate\View\View $view) {
            $view->with('settings', Setting::pluck('value', 'key'));
        });
    }
}
