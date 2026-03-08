<?php

use App\Http\Controllers\EventApiController;
use App\Http\Controllers\EventCalendarController;
use App\Http\Controllers\EventSubmissionController;
use App\Http\Controllers\ICalController;
use App\Http\Controllers\OrganizerDashboardController;
use App\Http\Controllers\OrganizerEventController;
use App\Http\Controllers\PremiumEventController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

// ─── Public Calendar ──────────────────────────────────────────────────────────
Route::middleware(['App\Http\Middleware\DetectEmbedMode', 'App\Http\Middleware\EmbedHeaders'])->group(function () {
    Route::get('/', [EventCalendarController::class, 'index'])->name('calendar.index');
    Route::get('/events/{event:slug}', [EventCalendarController::class, 'show'])->name('events.show');
    Route::get('/events/{event:slug}/ics', [ICalController::class, 'event'])->name('events.ics');
    Route::get('/feed.ics', [ICalController::class, 'feed'])->name('feed.ics');
});

// ─── Public API (AJAX, no auth) ───────────────────────────────────────────────
Route::prefix('api')->group(function () {
    Route::get('/events', [EventApiController::class, 'index'])->name('api.events.index');
    Route::get('/events/filter-options', [EventApiController::class, 'filterOptions'])->name('api.events.filter-options');
});

// ─── Event Submission ─────────────────────────────────────────────────────────
Route::get('/submit', [EventSubmissionController::class, 'create'])->name('submit.create');
Route::post('/submit', [EventSubmissionController::class, 'store'])->name('submit.store');
Route::get('/submit/verify/{token}', [EventSubmissionController::class, 'verify'])->name('submit.verify');
Route::get('/submit/success', [EventSubmissionController::class, 'success'])->name('submit.success');
Route::middleware('auth')->group(function () {
    Route::get('/submit/premium', [PremiumEventController::class, 'create'])->name('submit.premium.create');
    Route::post('/submit/premium', [PremiumEventController::class, 'store'])->name('submit.premium.store');
});

// ─── Stripe Webhook (no CSRF) ─────────────────────────────────────────────────
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])->name('stripe.webhook');

// ─── Organizer Dashboard (Phase 5) ───────────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    // Index — named 'dashboard' so Breeze's post-login redirect works
    Route::get('/dashboard', [OrganizerDashboardController::class, 'index'])->name('dashboard');

    Route::prefix('dashboard/events')->name('dashboard.events.')->group(function () {
        Route::get('/{event}', [OrganizerEventController::class, 'show'])->name('show');
        Route::get('/{event}/edit', [OrganizerEventController::class, 'edit'])->name('edit');
        Route::patch('/{event}', [OrganizerEventController::class, 'update'])->name('update');
        Route::delete('/{event}', [OrganizerEventController::class, 'destroy'])->name('destroy');
    });
});

// ─── Breeze Profile ───────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
