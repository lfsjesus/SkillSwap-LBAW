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
            $users = DB::table('users')->simplePaginate(20);
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

    public function showCreateUserForm() {

        if (!(Auth::guard('webadmin')->check())) {
            return redirect('/admin/login');
        }

        return view('pages.create-user-admin');
    }

    public function edit_user(Request $request) {
        if (!(Auth::guard('webadmin')->check())) {
            return redirect('/admin/login');
        }

        $id = $request->input('user_id');

        $user = User::find($id);


        // perform validation
        $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|email|max:50|unique:users,email,' . $id,
            'phone_number' => [
                'nullable',
                'regex:/^\+?\d+$/',
                'digits_between:8,15'
            ],
            'description' => 'nullable|string|max:500',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'username' => 'required|string|max:50|unique:users,username,' . $id,
            'birth_date' => 'nullable|date|before:18 years ago'
        ]);
        
        $user->name = ($request->input('name') != null) ? $request->input('name') : $user->name;
        $user->username = ($request->input('username') != null) ? $request->input('username') : $user->username;
        $user->email = ($request->input('email') != null) ? $request->input('email') : $user->email;
        $user->phone_number = ($request->input('phone_number') != null) ? $request->input('phone_number') : $user->phone_number;

        // parse date
        $user->birth_date = ($request->input('birth_date') != null) ?  date('Y-m-d', strtotime($request->input('birth_date'))) : $user->birth_date;
        $user->profile_picture = ($request->file('profile_picture') != null) ? 'data:image/png;base64,' . base64_encode(file_get_contents($request->file('profile_picture'))) : $user->profile_picture;
        
        $user->description = $request->input('description');


        $user->save();

        return redirect()->route('view-user-admin', ['username' => $user->username])->withSuccess('Profile updated successfully!');
    }

    public function create_user(Request $request) {
        if (!(Auth::guard('webadmin')->check())) {
            return redirect('/admin/login');
        }

        $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|email|max:50|unique:users,email',
            'phone_number' => [
                'nullable',
                'regex:/^\+?\d+$/',
                'digits_between:8,15'
            ],
            'description' => 'nullable|string|max:500',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'username' => 'required|string|max:50|unique:users,username',
            'birth_date' => 'required|date|before:18 years ago'
        ]);

        try {
        $user = new User();

        $user->name = $request->input('name');
        $user->username = $request->input('username');
        $user->email = $request->input('email');
        $user->phone_number = ($request->input('phone_number') != null) ? $request->input('phone_number') : null;
        $user->birth_date = date('Y-m-d', strtotime($request->input('birth_date')));
        $user->profile_picture = ($request->file('profile_picture') != null) ? 'data:image/png;base64,' . base64_encode(file_get_contents($request->file('profile_picture'))) : null;
        
        $password = $request->input('password');
        $user->password = bcrypt($password);

        $user->description = $request->input('description');

        $user->save();
        return redirect()->route('view-user-admin', ['username' => $user->username])->withSuccess('User created successfully!');
        } catch (\Exception $e) {
            return redirect()->route('create-user-form-admin')->withError('Unexpected error occurred while creating user!');
        }
    }


    //Uses full text search for name and username and exact match search for email
    public function search(Request $request)
    {   

        if (!(Auth::guard('webadmin')->check())) {
            return redirect('/admin/login');
        }

        $query = trim($request->input('q'));

        if (str_contains($query, '@')) {
            // If the query contains '@', perform an exact match search (assuming it's an email)
            $users = User::where('email', '=', $query)->get();
        } else {
            // Otherwise, perform a full-text search
            $users = User::query()
                        ->whereRaw("tsvectors @@ plainto_tsquery('english', ?)", [$query])
                        ->orderByRaw("ts_rank(tsvectors, plainto_tsquery('english', ?)) DESC", [$query])
                        ->get();
        }

        return view('pages.search-admin', ['users' => $users, 'query' => $query]);
    }
}
