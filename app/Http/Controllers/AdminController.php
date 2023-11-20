<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\Administrator;
use App\Models\User;
use Illuminate\Support\Facades\Redis;

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
        if (!(Auth::guard('webadmin')->check())) {
            return redirect('/admin/login');
        }

        $user = User::where('username', $username)->firstOrFail();
        $posts = $user->posts()->get();
        return view('pages.view-user-admin', ['user' => $user, 'posts' => $posts]);
    }


    public function showEditUserForm($username) {

        if (!(Auth::guard('webadmin')->check())) {
            return redirect('/admin/login');
        }
        
        $user = User::where('username', $username)->firstOrFail();
        return view('pages.edit-user-admin', ['user' => $user]);
    }

    public function edit_user(Request $request) {
        if (!(Auth::guard('webadmin')->check())) {
            return redirect('/admin/login');
        }

        $id = $request->input('user_id');

        $user = User::find($id);

        $user->name = ($request->input('name') != null) ? $request->input('name') : $user->name;
        $user->username = ($request->input('username') != null) ? $request->input('username') : $user->username;
        $user->email = ($request->input('email') != null) ? $request->input('email') : $user->email;
        $user->phone_number = ($request->input('phone_number') != null) ? $request->input('phone_number') : $user->phone_number;

        // parse date
        $user->birth_date = ($request->input('birth_date') != null) ?  date('Y-m-d', strtotime($request->input('birth_date'))) : $user->birth_date;
        $user->profile_picture = ($request->file('profile_picture') != null) ? 'data:image/png;base64,' . base64_encode(file_get_contents($request->file('profile_picture'))) : $user->profile_picture;
        
        $user->description = $request->input('description');


        $user->save();
        return redirect()->route('view-user-admin', ['username' => $user->username])->with('success', 'Profile edited successfully');
    }

}
