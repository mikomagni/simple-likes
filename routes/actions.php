<?php

use Illuminate\Support\Facades\Route;
use Mikomagni\SimpleLikes\Http\Controllers\SimpleLikesController;

/*
|--------------------------------------------------------------------------
| Simple Likes Action Routes
|--------------------------------------------------------------------------
|
| These routes handle the like toggle and stats API endpoints.
| They are prefixed with 'simple-likes' and use the 'web' middleware.
|
*/

// Dynamic throttling based on config
$toggleLimit = config('simple-likes.rate_limiting.like_toggle_limit', 30);
$statsLimit = config('simple-likes.rate_limiting.stats_limit', 120);
$statusLimit = config('simple-likes.rate_limiting.status_limit', 300);

// Like toggle endpoint - more restrictive throttling
Route::post('{id}/toggle', [SimpleLikesController::class, 'toggle'])
    ->middleware("throttle:{$toggleLimit},1")
    ->name('simple-likes.toggle');

// Status endpoint - returns count and liked state for client-side hydration
// Supports batched requests: /status?ids=abc,def,ghi
// High limit since this is called on every page load for hydration
Route::get('status', [SimpleLikesController::class, 'status'])
    ->middleware("throttle:{$statusLimit},1")
    ->name('simple-likes.status');

// Global analytics endpoints (read-only)
Route::get('global-stats', [SimpleLikesController::class, 'globalStats'])
    ->middleware("throttle:{$statsLimit},1")
    ->name('simple-likes.global-stats');

Route::get('popular', [SimpleLikesController::class, 'popular'])
    ->middleware("throttle:{$statsLimit},1")
    ->name('simple-likes.popular');

Route::get('activity', [SimpleLikesController::class, 'activity'])
    ->middleware("throttle:{$statsLimit},1")
    ->name('simple-likes.activity');

Route::get('weekly', [SimpleLikesController::class, 'weekly'])
    ->middleware("throttle:{$statsLimit},1")
    ->name('simple-likes.weekly');

Route::get('top-users', [SimpleLikesController::class, 'topUsers'])
    ->middleware("throttle:{$statsLimit},1")
    ->name('simple-likes.top-users');

// Combined stats endpoint (single request for dashboards)
Route::get('stats-all', [SimpleLikesController::class, 'statsAll'])
    ->middleware("throttle:{$statsLimit},1")
    ->name('simple-likes.stats-all');
