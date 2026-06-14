<?php

namespace App\Providers;

use App\Models\AppNotification;
use App\Models\Document;
use App\Models\Reservation;
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
        View::composer('layouts.admin', function ($view) {
            if (Auth::check() && Auth::user()->role === 'admin') {
                $pendingReservations = Reservation::where('status', 'pending')->count();
                $pendingDocuments = Document::where('status', 'pending')->count();
                $unreadNotifs = AppNotification::where('user_id', Auth::id())->where('is_read', false)->count();
                
                $view->with([
                    'pendingReservations' => $pendingReservations,
                    'pendingDocuments' => $pendingDocuments,
                    'unreadNotifs' => $unreadNotifs
                ]);
            } else {
                $view->with([
                    'pendingReservations' => 0,
                    'pendingDocuments' => 0,
                    'unreadNotifs' => 0
                ]);
            }
        });
    }
}
