<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WinnowingController;

Route::get('/winnow', [WinnowingController::class, 'index']);
Route::get('/winnowing/stream', [WinnowingController::class, 'stream']);
Route::get('/tree', function() {
    return \App\Models\Tree::all();
});
