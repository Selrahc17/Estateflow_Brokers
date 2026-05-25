<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('client.properties'));

// Auth Routes
Route::get('/login', fn() => view('pages.auth.login'))->name('auth.login');
Route::get('/register', fn() => view('pages.auth.register'))->name('auth.register');
Route::get('/forgot-password', fn() => view('pages.auth.forgot'))->name('auth.forgot');
Route::get('/verify-email', fn() => view('pages.auth.verify.index'))->name('auth.verify');
Route::get('/reset-password', fn() => view('pages.auth.reset.index'))->name('auth.reset');

// Public Client Routes
Route::get('/properties', fn() => view('pages.client.properties.index'))->name('client.properties');
Route::get('/properties/{slug}', fn($slug) => view('pages.client.properties.show', ['slug' => $slug]))->name('client.property.show');
Route::get('/about', fn() => view('pages.client.about.index'))->name('client.about');
Route::get('/contact', fn() => view('pages.client.contact.index'))->name('client.contact');
Route::get('/privacy-policy', fn() => view('pages.client.legal.privacy'))->name('client.legal.privacy');
Route::get('/terms-of-use', fn() => view('pages.client.legal.terms'))->name('client.legal.terms');
Route::get('/inquiry/success', fn() => view('pages.client.inquiry.success'))->name('client.inquiry.success');

// Client Account Routes
Route::prefix('my')->name('client.account.')->group(function () {
    Route::get('/', fn() => view('pages.client.dashboard.index'))->name('home');
    Route::get('/reservation', fn() => view('pages.client.reservation.index'))->name('reservation');
    Route::get('/payments', fn() => view('pages.client.payments.index'))->name('payments');
    Route::get('/payments/pay', fn() => view('pages.client.pay.index'))->name('payments.pay');
    Route::get('/payments/success', fn() => view('pages.client.pay.success'))->name('payments.success');
    Route::get('/documents', fn() => view('pages.client.documents.index'))->name('documents');
    Route::get('/notifications', fn() => view('pages.client.notifications.index'))->name('notifications');
    Route::get('/chat', fn() => view('pages.client.chat.index'))->name('chat');
    Route::get('/feedback', fn() => view('pages.client.feedback.index'))->name('feedback');
    Route::get('/profile', fn() => view('pages.client.profile.index'))->name('profile');
});

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', fn() => view('pages.admin.dashboard.index'))->name('dashboard');
    Route::get('/users', fn() => view('pages.admin.users.index'))->name('users');
    Route::get('/brokers', fn() => view('pages.admin.brokers.index'))->name('brokers');
    Route::get('/reservations', fn() => view('pages.admin.reservations.index'))->name('reservations');
    Route::get('/documents', fn() => view('pages.admin.documents.index'))->name('documents');
    Route::get('/payments', fn() => view('pages.admin.payments.index'))->name('payments');
    Route::get('/feedback', fn() => view('pages.admin.feedback.index'))->name('feedback');
    Route::get('/audit', fn() => view('pages.admin.audit.index'))->name('audit');
    Route::get('/reports', fn() => view('pages.admin.reports.index'))->name('reports');
    Route::get('/settings', fn() => view('pages.admin.settings.index'))->name('settings');
});

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
