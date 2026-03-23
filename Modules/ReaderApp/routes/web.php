<?php

use Illuminate\Support\Facades\Route;
use Modules\ReaderApp\Http\Controllers\ReaderAppController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('readerapps', ReaderAppController::class)->names('readerapp');
});
