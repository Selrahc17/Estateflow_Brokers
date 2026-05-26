<?php

use App\Http\Controllers\Admin\BrokerController as AdminBrokerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DocumentController as AdminDocumentController;
use App\Http\Controllers\Admin\AuditController as AdminAuditController;
use App\Http\Controllers\Admin\FeedbackController as AdminFeedbackController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\ReservationController as AdminReservationController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Broker\ChatController as BrokerChatController;
use App\Http\Controllers\Broker\ClientController as BrokerClientController;
use App\Http\Controllers\Broker\DashboardController as BrokerDashboardController;
use App\Http\Controllers\Broker\DocumentController as BrokerDocumentController;
use App\Http\Controllers\Broker\LotController;
use App\Http\Controllers\Broker\NotificationController as BrokerNotificationController;
use App\Http\Controllers\Broker\PaymentController as BrokerPaymentController;
use App\Http\Controllers\Broker\PropertyController;
use App\Http\Controllers\Broker\ReportController as BrokerReportController;
use App\Http\Controllers\Broker\ReservationController as BrokerReservationController;
use App\Http\Controllers\Broker\SettingController as BrokerSettingController;
use App\Http\Controllers\Client\ChatController as ClientChatController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\DocumentController as ClientDocumentController;
use App\Http\Controllers\Client\FeedbackController as ClientFeedbackController;
use App\Http\Controllers\Client\NotificationController as ClientNotificationController;
use App\Http\Controllers\Client\PaymentController as ClientPaymentController;
use App\Http\Controllers\Client\ProfileController as ClientProfileController;
use App\Http\Controllers\Client\PropertyController as ClientPropertyController;
use App\Http\Controllers\Client\ReservationController as ClientReservationController;
use Illuminate\Support\Facades\Route;

// ===================== AUTH ROUTES =====================
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('auth.login');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login.post');
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('auth.register');
Route::post('/register', [AuthController::class, 'register'])->name('auth.register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

// ===================== PUBLIC CLIENT ROUTES =====================
Route::redirect('/', '/properties');

Route::get('/properties', [ClientPropertyController::class, 'index'])->name('client.properties');
Route::get('/properties/{slug}', [ClientPropertyController::class, 'show'])->name('client.property.show');
Route::view('/about', 'pages.client.about.index')->name('client.about');
Route::view('/contact', 'pages.client.contact.index')->name('client.contact');
Route::view('/privacy-policy', 'pages.client.legal.privacy')->name('client.legal.privacy');
Route::view('/terms-of-use', 'pages.client.legal.terms')->name('client.legal.terms');
Route::view('/inquiry/success', 'pages.client.inquiry.success')->name('client.inquiry.success');

// ===================== AUTHENTICATED CLIENT ROUTES =====================
Route::prefix('my')->name('client.account.')->middleware(['auth', 'role:client'])->group(function () {
    Route::get('/', [ClientDashboardController::class, 'index'])->name('home');
    Route::get('/reservation', [ClientReservationController::class, 'index'])->name('reservation');
    Route::get('/payments', [ClientPaymentController::class, 'index'])->name('payments');
    Route::get('/payments/pay', [ClientPaymentController::class, 'pay'])->name('payments.pay');
    Route::post('/payments', [ClientPaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/success', fn() => view('pages.client.pay.success'))->name('payments.success');
    Route::get('/documents', [ClientDocumentController::class, 'index'])->name('documents');
    Route::post('/documents', [ClientDocumentController::class, 'store'])->name('documents.store');
    Route::get('/notifications', [ClientNotificationController::class, 'index'])->name('notifications');
    Route::post('/notifications/read-all', [ClientNotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('/chat', [ClientChatController::class, 'index'])->name('chat');
    Route::post('/chat', [ClientChatController::class, 'store'])->name('chat.store');
    Route::get('/feedback', [ClientFeedbackController::class, 'index'])->name('feedback');
    Route::post('/feedback', [ClientFeedbackController::class, 'store'])->name('feedback.store');
    Route::get('/profile', [ClientProfileController::class, 'index'])->name('profile');
    Route::post('/profile', [ClientProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [ClientProfileController::class, 'updatePassword'])->name('profile.password');
});

// ===================== ADMIN ROUTES =====================
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [AdminUserController::class, 'index'])->name('users');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    Route::get('/brokers', [AdminBrokerController::class, 'index'])->name('brokers');
    Route::get('/brokers/create', [AdminBrokerController::class, 'create'])->name('brokers.create');
    Route::post('/brokers', [AdminBrokerController::class, 'store'])->name('brokers.store');
    Route::get('/brokers/{user}', [AdminBrokerController::class, 'show'])->name('brokers.show');
    Route::get('/reservations', [AdminReservationController::class, 'index'])->name('reservations');
    Route::get('/reservations/{reservation}', [AdminReservationController::class, 'show'])->name('reservations.show');
    Route::patch('/reservations/{reservation}/status', [AdminReservationController::class, 'updateStatus'])->name('reservations.status');
    Route::get('/documents', [AdminDocumentController::class, 'index'])->name('documents');
    Route::post('/documents/{document}/verify', [AdminDocumentController::class, 'verify'])->name('documents.verify');
    Route::post('/documents/{document}/reject', [AdminDocumentController::class, 'reject'])->name('documents.reject');
    Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments');
    Route::get('/feedback', [AdminFeedbackController::class, 'index'])->name('feedback');
    Route::post('/feedback/{feedback}/resolve', [AdminFeedbackController::class, 'resolve'])->name('feedback.resolve');
    Route::get('/audit', [AdminAuditController::class, 'index'])->name('audit');
    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports');
    Route::get('/settings', [AdminSettingController::class, 'index'])->name('settings');
});

// ===================== BROKER ROUTES =====================
Route::prefix('broker')->name('broker.')->middleware(['auth', 'role:broker'])->group(function () {
    Route::get('/dashboard', [BrokerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/properties', [PropertyController::class, 'index'])->name('properties.index');
    Route::get('/properties/create', [PropertyController::class, 'create'])->name('properties.create');
    Route::post('/properties', [PropertyController::class, 'store'])->name('properties.store');
    Route::get('/properties/{property}', [PropertyController::class, 'show'])->name('properties.show');
    Route::get('/properties/{property}/edit', [PropertyController::class, 'edit'])->name('properties.edit');
    Route::put('/properties/{property}', [PropertyController::class, 'update'])->name('properties.update');
    Route::delete('/properties/{property}', [PropertyController::class, 'destroy'])->name('properties.destroy');
    Route::get('/lots', [LotController::class, 'index'])->name('lots.index');
    Route::get('/lots/create', [LotController::class, 'create'])->name('lots.create');
    Route::post('/lots', [LotController::class, 'store'])->name('lots.store');
    Route::get('/lots/{lot}/edit', [LotController::class, 'edit'])->name('lots.edit');
    Route::put('/lots/{lot}', [LotController::class, 'update'])->name('lots.update');
    Route::delete('/lots/{lot}', [LotController::class, 'destroy'])->name('lots.destroy');
    Route::get('/reservations', [BrokerReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/create', [BrokerReservationController::class, 'create'])->name('reservations.create');
    Route::post('/reservations', [BrokerReservationController::class, 'store'])->name('reservations.store');
    Route::get('/reservations/{reservation}', [BrokerReservationController::class, 'show'])->name('reservations.show');
    Route::patch('/reservations/{reservation}/status', [BrokerReservationController::class, 'updateStatus'])->name('reservations.status');
    Route::get('/clients', [BrokerClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/create', [BrokerClientController::class, 'create'])->name('clients.create');
    Route::post('/clients', [BrokerClientController::class, 'store'])->name('clients.store');
    Route::get('/clients/{client}', [BrokerClientController::class, 'show'])->name('clients.show');
    Route::get('/clients/{client}/edit', [BrokerClientController::class, 'edit'])->name('clients.edit');
    Route::put('/clients/{client}', [BrokerClientController::class, 'update'])->name('clients.update');
    Route::get('/payments', [BrokerPaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments/{payment}/verify', [BrokerPaymentController::class, 'verify'])->name('payments.verify');
    Route::post('/payments/{payment}/reject', [BrokerPaymentController::class, 'reject'])->name('payments.reject');
    Route::get('/documents', [BrokerDocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents/{document}/verify', [BrokerDocumentController::class, 'verify'])->name('documents.verify');
    Route::post('/documents/{document}/reject', [BrokerDocumentController::class, 'reject'])->name('documents.reject');
    Route::get('/notifications', [BrokerNotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [BrokerNotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [BrokerNotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/send', [BrokerNotificationController::class, 'send'])->name('notifications.send');
    Route::get('/chat', [BrokerChatController::class, 'index'])->name('chat.index');
    Route::post('/chat', [BrokerChatController::class, 'store'])->name('chat.store');
    Route::get('/reports', [BrokerReportController::class, 'index'])->name('reports.index');
    Route::get('/settings', [BrokerSettingController::class, 'index'])->name('settings.index');
    Route::post('/settings/profile', [BrokerSettingController::class, 'updateProfile'])->name('settings.profile');
    Route::post('/settings/password', [BrokerSettingController::class, 'updatePassword'])->name('settings.password');
});