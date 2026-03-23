<?php

use Illuminate\Support\Facades\Route;
use Modules\PublisherWorkspace\Http\Controllers\PublisherWorkspaceController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('publisherworkspaces', PublisherWorkspaceController::class)->names('publisherworkspace');
});
Route::get('/test-register', function () {
    // استخدمنا اسم الوحدة متبوعاً بـ :: ثم اسم الملف
    return view('publisherworkspace::test-register');
});
