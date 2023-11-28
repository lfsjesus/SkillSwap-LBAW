<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CardController;
use App\Http\Controllers\ItemController;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GroupController;
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


Route::controller(UserController::class)->group(function () {
    Route::get('/user/{username}', 'show')->name('user');
    Route::get('/user/{username}/edit', 'showEditForm')->name('edit_profile');
    Route::put('/user/edit', 'edit')->name('edit_user');
    Route::get('/search', 'search')->name('search');
});

Route::controller(AdminController::class)->group(function () {
    Route::get('/admin/users/search', 'search')->name('admin-search');
    Route::get('/admin/{username}', 'show_user')->name('view-user-admin');  
    Route::get('/admin/{username}/edit', 'showEditUserForm')->name('edit-user-form-admin');
    Route::get('/admin/user/create', 'showCreateUserForm')->name('create-user-form-admin');
    Route::post('/admin/create', 'create_user')->name('create_user_admin');
    Route::put('/admin/edit', 'edit_user')->name('edit_profile_admin');
});

Route::controller(GroupController::class)->group(function () {
    Route::get('/groups/{id}', 'show')->name('group');
    /*
    Route::get('/groups/{id}', 'show')->name('group');
    Route::get('/groups/{id}/edit', 'showEditForm')->name('edit_group');
    Route::put('/groups/edit', 'edit')->name('edit_group');
    Route::post('/groups/create', 'create')->name('create_group');
    Route::delete('/groups/delete', 'delete')->name('delete_group');
    */
});




