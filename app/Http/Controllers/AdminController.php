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
        if (!(Auth::guard('webadmin')->check())) {
            return redirect('/admin/login');
        }

        $user = User::where('username', $username)->firstOrFail();
        $posts = $user->posts()->get();
        return view('pages.view-user-admin', ['user' => $user, 'posts' => $posts]);
    }

    public function edit_user($username) {
        if (!(Auth::guard('webadmin')->check())) {
            return redirect('/admin/login');
        }
        
        $user = User::where('username', $username)->firstOrFail();
        return view('pages.edit-user-admin', ['user' => $user]);
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
    
            return view('pages.search-admin', compact('users'));
        }
}
