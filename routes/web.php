<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\MailController;

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

// Static pages
Route::view('/about', 'pages.about')->name('about');
Route::view('/contacts', 'pages.contacts')->name('contacts');
Route::view('/mainFeatures', 'pages.mainFeatures')->name('mainFeatures');
Route::view('/settings', 'pages.settings')->name('settings');

Route::controller(PostController::class)->group(function () {
    Route::get('/home',  'list')->name('home');
    Route::post('/posts/create', 'create')->name('create_post');
    Route::put('/posts/edit', 'edit')->name('edit_post');
    Route::delete('/posts/delete', 'delete')->name('delete_post');
    Route::get('/posts', 'list')->name('posts');
    Route::get('/posts/{id}', 'show')->name('post');
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

// Registration
Route::controller(RegisterController::class)->group(function () {
    Route::get('/register', 'showRegistrationForm')->name('register');
    Route::post('/register', 'register');
});

// User
Route::controller(UserController::class)->group(function () {
    Route::get('/user/{username}', 'show')->name('user');
    Route::get('/user/{username}/edit', 'showEditForm')->name('edit_profile');
    Route::put('/user/edit', 'edit')->name('edit_user');
    Route::delete('/user/delete', 'userDelete')->name('delete_user');
    Route::get('/user/{username}/friends', 'showFriends')->name('user_friends');
    Route::get('/user/{username}/groups', 'showGroups')->name('user_groups');
    Route::post('/friend/request', 'sendFriendRequest')->name('send_friend_request');
    Route::delete('/friend/cancel_request', 'cancelFriendRequest')->name('cancel_friend_request');
    Route::post('/friend/accept_request', 'acceptFriendRequest')->name('accept_friend_request');
    Route::delete('/friend/remove', 'removeFriend')->name('remove_friend');
    Route::delete('/friend/reject_request', 'rejectFriendRequest')->name('reject_friend_request');

});

// Admin
Route::controller(AdminController::class)->group(function () {
    Route::get('/admin/home', 'show')->name('admin');
    Route::get('/admin/{username}', 'showUser')->name('view-user-admin');  
    Route::post('/admin/{username}/ban', 'banUser')->name('ban-user-admin');
    Route::post('/admin/{username}/unban', 'unbanUser')->name('unban-user-admin');
    Route::get('/admin/{username}/edit', 'showEditUserForm')->name('edit-user-form-admin');
    Route::get('/admin/user/create', 'showCreateUserForm')->name('create-user-form-admin');
    Route::get('/admin/groups/{id}/edit', 'showEditGroupForm')->name('edit-group-form-admin');
    Route::post('/admin/create', 'createUser')->name('create_user_admin');
    Route::put('/admin/user/edit', 'editUser')->name('edit_user_admin');
    Route::delete('/admin/delete', 'deleteUser')->name('delete_user_admin');
    Route::get('/admin/groups/list', 'listGroups')->name('admin-groups');
    Route::get('/admin/groups/{id}', 'showGroup')->where('id', '[0-9]+')->name('view-group-admin');
});

// Group
Route::controller(GroupController::class)->group(function () {
    Route::get('/groups', 'list')->name('groups');
    Route::get('/group/{id}', 'show')->where('id', '[0-9]+')->name('group');
    Route::get('/create', 'showCreateForm')->name('create_group_form');
    Route::get('/group/{id}/edit', 'showEditForm')->name('edit_group_form');
    Route::post('/group/create', 'create')->name('create_group');
    Route::put('/group/edit', 'edit')->name('edit_group');
    Route::delete('/group/delete', 'deleteGroup')->name('delete_group');
    Route::get('/group/{groupId}/members', 'showMembers')->name('group_members');
    Route::get('/group/{groupId}/owners', 'showOwners')->name('group_owners');
    Route::post('/group/join-request', 'sendJoinGroupRequest')->name('join_group_request');
    Route::delete('/group/cancel-join-request', 'cancelJoinGroupRequest')->name('cancel_join_group_request');
    Route::post('/group/accept-join-request', 'acceptJoinGroupRequest')->name('accept_join_group_request');
    Route::delete('/group/reject-join-request', 'rejectJoinGroupRequest')->name('reject_join_group_request');
    Route::delete('/group/leave', 'leaveGroup')->name('leave_group');
    Route::post('/group/addMember', 'addMember')->name('add_member');
    Route::post('/group/addOwner', 'addOwner')->name('add_owner');
    Route::delete('/group/removeMember', 'removeMember')->name('remove_member');
    Route::delete('/group/removeOwner', 'removeOwner')->name('remove_owner');
});

// Like
Route::controller(LikeController::class)->group(function () {
    Route::post('/posts/like', 'likePost')->name('like_post');
    Route::post('/comments/like', 'likeComment')->name('like_comment');
});

// Comment
Route::controller(CommentController::class)->group(function () {
    Route::post('/posts/comment', 'createComment')->name('create_comment');
    Route::delete('/posts/comment/delete', 'deleteComment')->name('delete_comment');
    Route::put('/posts/comment/edit', 'editComment')->name('edit_comment');
});

// Notification
Route::controller(NotificationController::class)->group(function () {
    Route::put('/notifications/markAsRead', 'markAsRead')->name('mark_as_read');
    Route::put('/notifications/markAllAsRead', 'markAllAsRead')->name('mark_all_as_read');
});

// Search
Route::controller(SearchController::class)->group(function () {
    Route::get('/search', 'search')->name('search');
});

// Mail
Route::controller(MailController::class)->group(function () {
    Route::get('/contact', 'showContactForm')->name('contact.show');
    Route::post('/send', 'send')->name('send');
    Route::get('/password/reset/{token}', 'showResetForm')->name('password.reset');
    Route::post('/password/reset', 'reset')->name('password.update');
});