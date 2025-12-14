<?php

use Illuminate\Support\Facades\Route;
use Mikomagni\SimpleLikes\Http\CssAsset;

/*
|--------------------------------------------------------------------------
| Simple Likes Web Routes
|--------------------------------------------------------------------------
|
| These routes handle public-facing endpoints like dynamic CSS.
|
*/

// Dynamic CSS route for widgets
Route::get('simple-likes/widgets.css', function () {
    $css = CssAsset::generateDynamicCss();

    return response($css, 200, [
        'Content-Type' => 'text/css',
        'Cache-Control' => 'public, max-age=3600',
    ]);
})->name('simple-likes.dynamic-css');
