<?php

namespace App\Providers;

use App\Models\AppNotification;
use App\Models\Document;
use App\Models\Reservation;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        View::composer(['layouts.app', 'layouts.broker', 'layouts.client', 'layouts.public'], function ($view) {
            $view->with('appLogo', Setting::get('logo_url'));
        });

        View::composer('layouts.admin', function ($view) {
            $appLogo = Setting::get('logo_url');
            if (Auth::check() && Auth::user()->role === 'admin') {
                $pendingReservations = Reservation::where('status', 'pending')->count();
                $pendingDocuments = Document::where('status', 'pending')->count();
                $unreadNotifs = AppNotification::where('user_id', Auth::id())->where('is_read', false)->count();

                $view->with([
                    'appLogo' => $appLogo,
                    'pendingReservations' => $pendingReservations,
                    'pendingDocuments' => $pendingDocuments,
                    'unreadNotifs' => $unreadNotifs,
                ]);
            } else {
                $view->with([
                    'appLogo' => $appLogo,
                    'pendingReservations' => 0,
                    'pendingDocuments' => 0,
                    'unreadNotifs' => 0,
                ]);
            }
        });
    }
}
