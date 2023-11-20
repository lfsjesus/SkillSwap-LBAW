<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CardController;
use App\Http\Controllers\ItemController;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AdminLoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Home
Route::redirect('/', '/login');
Route::redirect('/admin', '/admin/login');

Route::get('/home', [PostController::class, 'list'])->name('home');

// Admin
Route::get('/admin/home', [AdminController::class, 'show'])->name('admin');


Route::controller(PostController::class)->group(function () {
    Route::post('/posts/create', 'create')->name('create_post');
    Route::put('/posts/edit', 'edit')->name('edit_post');
    Route::delete('/posts/delete', 'delete')->name('delete_post');
    Route::get('/posts', 'list')->name('posts');
    Route::get('/posts/{id}', 'show');
});

// API
Route::controller(CardController::class)->group(function () {
    Route::put('/api/cards', 'create');
    Route::delete('/api/cards/{card_id}', 'delete');
});

Route::controller(ItemController::class)->group(function () {
    Route::put('/api/cards/{card_id}', 'create');
    Route::post('/api/item/{id}', 'update');
    Route::delete('/api/item/{id}', 'delete');
});



// Authentication
Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'authenticate');
    Route::get('/logout', 'logout')->name('logout');
});

// Authentication
Route::controller(AdminLoginController::class)->group(function () {
    Route::get('/admin/login', 'showLoginForm')->name('admin.login');
    Route::post('/admin/login', 'authenticate')->name('admin.authenticate');
    Route::get('/admin/logout', 'logout')->name('admin.logout');
});

Route::controller(RegisterController::class)->group(function () {
    Route::get('/register', 'showRegistrationForm')->name('register');
    Route::post('/register', 'register');
});


// User profile
Route::get('/user/{username}', [UserController::class, 'show'])->name('user');

// User profile - admin
Route::get('/admin/{username}', [AdminController::class, 'show_user'])->name('view-user-admin');

//Edit User profile
Route::get('/user/{username}/edit', [UserController::class, 'edit'])->name('edit_profile');

//Edit User profile - admin
Route::get('/admin/{username}/edit', [AdminController::class, 'edit_user'])->name('edit_profile_admin');

/*
//Exact Match Search
Route::get('/search', [UserController::class, 'exactMatchSearch'])->name('search');

//Full Text Search
Route::get('/search', [UserController::class, 'fullTextSearch'])->name('search');

*/

//Search
Route::get('/search', [UserController::class, 'search'])->name('search');

//Exact Match Search - Admin
Route::get('/admin/search', [AdminController::class, 'adminSearch'])->name('search-admin');
