<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HotelController;

// Route::get('/', function () {
//     return view('client.index');
// });

Route::get('/', [HotelController::class, 'index'])->name('index');

Route::get('/rooms', [HotelController::class, 'rooms'])->name('rooms');

Route::get('/restaurant', [HotelController::class, 'restaurant'])->name('restaurant');

Route::get('/blog', [HotelController::class, 'blog'])->name('blog');

Route::get('/about', [HotelController::class, 'about'])->name('about');

Route::get('/contact', [HotelController::class, 'contact'])->name('contact');

Route::get('/rooms-single', [HotelController::class, 'rooms-single'])->name('rooms-single');

Route::get('/blog-single', [HotelController::class, 'blog-single'])->name('blog-single');