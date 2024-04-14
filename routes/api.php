<?php

use App\Http\Controllers\Api\MainController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::get('/get_wallpapers', [MainController::class , 'get_wallpapers'] );
Route::get('/get_new_wallpapers', [MainController::class , 'get_new_wallpapers'] );
Route::get('/get_wallpaper_details', [MainController::class , 'get_wallpaper_details'] );
Route::get('/get_one_wallpaper', [MainController::class , 'get_one_wallpaper'] );
Route::get('/get_categories', [MainController::class , 'get_categories'] );
Route::get('/get_category_details', [MainController::class , 'get_category_details'] );
Route::get('/get_search', [MainController::class , 'get_search'] );
Route::get('/get_search_category', [MainController::class , 'get_search_category'] );
Route::post('/update_view', [MainController::class , 'update_view'] );
Route::post('/update_download', [MainController::class , 'update_download'] );
Route::get('/get_ads', [MainController::class , 'get_ads'] );
Route::get('/get_settings', [MainController::class , 'get_settings'] );