<?php

use Illuminate\Support\Facades\Route;
use Modules\SaaSAdmin\Http\Controllers\SaaSAdminController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('saasadmins', SaaSAdminController::class)->names('saasadmin');
});
