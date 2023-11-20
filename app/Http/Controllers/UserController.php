<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\User;


class UserController extends Controller
{
    public function show(string $username) {
        $user = User::where('username', $username)->firstOrFail();
        $posts = $user->posts()->get();
        return view('pages.user', ['user' => $user, 'posts' => $posts]);
    }


    public function edit(string $username) {
        $user = User::where('username', $username)->firstOrFail();
        return view('pages.editProfile', ['user' => $user]);
    }

    public function exactMatchSearch(Request $request)
    {
        $query = $request->input('q');

        // Performing an exact match search
        $users = User::where('username', '=', $query)
                    ->orWhere('email', '=', $query)
                    ->get();

        return view('users.search', compact('users')); // Use the appropriate view for displaying search results
    }
}