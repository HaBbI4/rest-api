<?php

use Illuminate\Support\Facades\Route;
use Webkul\RestApi\Http\Controllers\V1\Shop\Blog\BlogController;

Route::group(['middleware' => ['locale', 'theme', 'currency']], function () {
    /**
     * Blog routes.
     */
    Route::get('blogs', [BlogController::class, 'index']);
    Route::get('blogs/{id}', [BlogController::class, 'show']);
});
