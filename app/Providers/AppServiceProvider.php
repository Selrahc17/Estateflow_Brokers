<?php

namespace App\Providers;

use App\Models\AppNotification;
use App\Models\AuditLog;
use App\Models\ContactMessage;
use App\Models\Document;
use App\Models\Feedback;
use App\Models\Lot;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\User;
use App\Support\Totals;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
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
        $this->configureRateLimiting();
        $this->recordSignInsAndSignOuts();
        $this->recordSalesWhenMarkedSold();

        View::composer(['layouts.app', 'layouts.broker', 'layouts.client', 'layouts.public', 'layouts.auth'], function ($view) {
            $view->with('appLogo', Setting::get('logo_url', null));
        });

        View::composer('layouts.admin', function ($view) {
            $appLogo = Setting::get('logo_url', null);
            if (Auth::check() && Auth::user()->role === 'admin') {
                // The sidebar badges, fetched in one round trip instead of six.
                $view->with(['appLogo' => $appLogo, ...Totals::of([
                    'pendingReservations' => Reservation::where('status', 'pending')->selectRaw('count(*)'),
                    'pendingDocuments' => Document::whereNull('client_id')->where('status', 'pending')->selectRaw('count(*)'),
                    'unreadNotifs' => AppNotification::where('user_id', Auth::id())->where('is_read', false)->selectRaw('count(*)'),
                    'pendingBrokers' => User::where('role', 'broker')->where('is_approved', false)->selectRaw('count(*)'),
                    'pendingFeedback' => Feedback::where('status', 'pending')->selectRaw('count(*)'),
                    'newContactMessages' => ContactMessage::where('status', 'new')->selectRaw('count(*)'),
                ])]);
            } else {
                $view->with([
                    'appLogo' => $appLogo,
                    'pendingReservations' => 0,
                    'pendingDocuments' => 0,
                    'unreadNotifs' => 0,
                    'pendingBrokers' => 0,
                    'pendingFeedback' => 0,
                    'newContactMessages' => 0,
                ]);
            }
        });
    }

    /**
     * Marking a listing or lot as Sold adds it to the broker's commission table;
     * undoing Sold removes that row while the broker has not worked on it yet.
     * A listing with lots is sold lot by lot, so only its lots create rows.
     */
    private function recordSalesWhenMarkedSold(): void
    {
        Property::updated(function (Property $property) {
            if (! $property->wasChanged('status')) {
                return;
            }
            if ($property->status === 'sold' && ! $property->lots()->exists()) {
                Sale::recordFromListing($property);
            } elseif ($property->getOriginal('status') === 'sold') {
                Sale::undoFromListing($property);
            }
        });

        Lot::updated(function (Lot $lot) {
            if (! $lot->wasChanged('status') || ! $lot->property) {
                return;
            }
            if ($lot->status === 'sold') {
                Sale::recordFromListing($lot->property, $lot);
            } elseif ($lot->getOriginal('status') === 'sold') {
                Sale::undoFromListing($lot->property, $lot);
            }
        });
    }

    private function recordSignInsAndSignOuts(): void
    {
        $record = fn (?User $user, string $action, string $route) => $user && User::whereKey($user->getKey())->exists() && AuditLog::create([
            'actor_id' => $user->id,
            'actor_role' => $user->role,
            'method' => 'POST',
            'route_name' => $route,
            'action' => $action,
            'description' => strtolower($action),
            'status_code' => 200,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        Event::listen(Login::class, fn (Login $event) => $record($event->user, 'Signed in', 'auth.login'));
        Event::listen(Logout::class, fn (Logout $event) => $record($event->user, 'Signed out', 'auth.logout'));
    }

    private function configureRateLimiting(): void
    {
        // Show the limit as a form error rather than a bare 429 page.
        $tooManyAttempts = function (Request $request, array $headers) {
            $seconds = (int) ($headers['Retry-After'] ?? 60);

            return back()
                ->withErrors(['email' => "Too many attempts. Please wait {$seconds} seconds and try again."])
                ->withInput($request->except(['password', 'password_confirmation']));
        };

        $emailAndIp = function (Request $request): string {
            $email = $request->input('email');

            return (is_string($email) ? strtolower($email) : '').'|'.$request->ip();
        };

        RateLimiter::for('login', fn (Request $request) => [
            Limit::perMinute(5)->by($emailAndIp($request))->response($tooManyAttempts),
            Limit::perMinute(20)->by($request->ip())->response($tooManyAttempts),
        ]);

        RateLimiter::for('password-reset', fn (Request $request) => [
            Limit::perMinute(3)->by($emailAndIp($request))->response($tooManyAttempts),
            Limit::perMinute(10)->by($request->ip())->response($tooManyAttempts),
        ]);

        RateLimiter::for('register', fn (Request $request) => [
            Limit::perMinute(10)->by($request->ip())->response($tooManyAttempts),
        ]);

        RateLimiter::for('public-forms', fn (Request $request) => [
            Limit::perMinute(5)->by($request->ip())->response($tooManyAttempts),
        ]);
    }
}
