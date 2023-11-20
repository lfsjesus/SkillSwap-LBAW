<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\Administrator;
use App\Models\User;


class AdminController extends Controller
{
    public function show() {

        if (!(Auth::guard('webadmin')->check())) {
            return redirect('/admin/login');
        }

        else{
            $username = Auth::guard('webadmin')->user()->username;
            $admin = Administrator::where('username', $username)->firstOrFail();
            $users = DB::table('users')->get();
            return view('pages.admin', ['admin' => $admin, 'users' => $users]);
        }
    }

    public function show_user($username) {
        $user = User::where('username', $username)->firstOrFail();
        $posts = $user->posts()->get();
        return view('pages.view-user-admin', ['user' => $user, 'posts' => $posts]);
    }

    public function edit_user($username) {
        $user = User::where('username', $username)->firstOrFail();
        return view('pages.edit-user-admin', ['user' => $user]);
    }

}
