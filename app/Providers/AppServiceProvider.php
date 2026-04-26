<?php

namespace App\Providers;

use App\Constants\CommonConstant;
use App\Models\GeneralSetting;
use App\Models\Language;
use App\Models\Sales;
use App\Services\SteadfastCourierService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Opcodes\LogViewer\Facades\LogViewer;
use SteadFast\SteadFastCourierLaravelPackage\SteadfastCourier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(SteadfastCourier::class, SteadfastCourierService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (Schema::hasTable('general_settings')) {
            $general = GeneralSetting::first();
            view()->share('general', $general);
        }
        View::composer('backend.layout.navbar', function ($view) {
            $user = auth()->guard('admin')->user();

            $notifications = $user->notifications()->latest()->take(15)->get();
            $unreadCount = $user->unreadNotifications()->count();

            $view->with([
                'notifications' => $notifications,
                'unreadCount' => $unreadCount,
            ]);
        });

        if (Schema::hasTable('languages')) {
            // Cache all languages for 60 minutes
            $allLanguages = Cache::remember('all_languages', 60, function () {
                return Language::orderBy('created_at', 'desc')->get();
            });

            // Share the languages with views
            view()->share('language_top', $allLanguages);
        }

        LogViewer::auth(function ($request) {
            return auth()->guard('admin')->check();
        });

        View::composer(['backend.layout.sidebar', 'backend.layout.navbar'], function ($view) {
            if (Schema::hasTable('sales')) {
                $draftSales = Sales::where('system_status', CommonConstant::DRAFT)->count();
                $view->with('draftSales', $draftSales);
            }
        });
    }
}
