<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('client.properties'));

// Auth Routes
Route::get('/login', fn() => view('pages.auth.login'))->name('auth.login');
Route::get('/register', fn() => view('pages.auth.register'))->name('auth.register');
Route::get('/forgot-password', fn() => view('pages.auth.forgot'))->name('auth.forgot');

// Broker Routes
Route::get('/broker/dashboard', fn() => view('pages.dashboard.index'))->name('dashboard');
Route::get('/broker/properties', fn() => view('pages.properties.index'))->name('properties.index');
Route::get('/broker/lots', fn() => view('pages.lots.index'))->name('lots.index');
Route::get('/broker/reservations', fn() => view('pages.reservations.index'))->name('reservations.index');
Route::get('/broker/clients', fn() => view('pages.clients.index'))->name('clients.index');
Route::get('/broker/payments', fn() => view('pages.payments.index'))->name('payments.index');
Route::get('/broker/documents', fn() => view('pages.documents.index'))->name('documents.index');
Route::get('/broker/notifications', fn() => view('pages.notifications.index'))->name('notifications.index');
Route::get('/broker/chat', fn() => view('pages.chat.index'))->name('chat.index');
Route::get('/broker/reports', fn() => view('pages.reports.index'))->name('reports.index');
Route::get('/broker/settings', fn() => view('pages.settings.index'))->name('settings.index');

// Public Client Routes (no login required)
Route::get('/properties', fn() => view('pages.client.properties.index'))->name('client.properties');
Route::get('/properties/{slug}', fn($slug) => view('pages.client.properties.show', ['slug' => $slug]))->name('client.property.show');

// Client Account Routes (login required)
Route::prefix('my')->name('client.account.')->group(function () {
    Route::get('/', fn() => view('pages.client.dashboard.index'))->name('home');
    Route::get('/reservation', fn() => view('pages.client.reservation.index'))->name('reservation');
    Route::get('/payments', fn() => view('pages.client.payments.index'))->name('payments');
    Route::get('/documents', fn() => view('pages.client.documents.index'))->name('documents');
    Route::get('/notifications', fn() => view('pages.client.notifications.index'))->name('notifications');
    Route::get('/chat', fn() => view('pages.client.chat.index'))->name('chat');
});
