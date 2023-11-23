<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserController extends Model 
{
    public function show(string $username) {
        if(!Auth::check()) {
            if(User::where('username', $username)->firstOrFail()->public_profile == false) {
                return redirect()->route('home')->with('error', 'You cannot view this profile');
            }
            $user = User::where('username', $username)->firstOrFail();
            $posts = $user->posts()->get();
            return view('pages.user', ['user' => $user, 'posts' => $posts]);
        }
        $user = User::where('username', $username)->firstOrFail();
        $posts = $user->posts()->get();
        return view('pages.user', ['user' => $user, 'posts' => $posts]);

    }


    public function showEditForm($username) {
        
        if(!Auth::check()) {
            return redirect()->route('home')->with('error', 'You cannot edit this profile');
        }

        if(Auth::user()->username != $username) {
            return redirect()->route('home')->with('error', 'You cannot edit this profile');
        }

        $user = User::find(Auth::user()->id);
        return view('pages.editProfile', ['user' => $user]);
    }

    public function edit(Request $request) {
        $user = User::find(Auth::user()->id);

        
        if (Auth::user()->id != $user->id) {
            return redirect()->back()->with('error', 'You cannot edit this user');
        }

        $user->name = ($request->input('name') != null) ? $request->input('name') : $user->name;
        $user->username = ($request->input('username') != null) ? $request->input('username') : $user->username;
        $user->email = ($request->input('email') != null) ? $request->input('email') : $user->email;
        $user->phone_number = ($request->input('phone_number') != null) ? $request->input('phone_number') : $user->phone_number;
        $user->birth_date = ($request->input('birth_date') != null) ? $request->input('birth_date') : $user->birth_date;
        $user->profile_picture = ($request->file('profile_picture') != null) ? 'data:image/png;base64,' . base64_encode(file_get_contents($request->file('profile_picture'))) : $user->profile_picture;
        
        $user->description = $request->input('description');


        $user->save();
        return redirect()->route('user', ['username' => $user->username])->with('success', 'Profile edited successfully');
    }


    /*

    public function exactMatchSearch(Request $request)
    {
        $query = $request->input('q');

        // Performing an exact match search
        $users = User::where('username', '=', $query)
                    ->orWhere('email', '=', $query)
                    ->get();

        return view('pages.exactMatchSearchResults', ['users' => $users, 'query' => $query]);

    }

    public function fullTextSearch(Request $request)
    {
        $query = trim($request->input('q')); // Trim spaces from the beginning and end

        $users = User::query()
            ->whereRaw("tsvectors @@ plainto_tsquery('english', ?)", [$query])
            ->orderByRaw("ts_rank(tsvectors, plainto_tsquery('english', ?)) DESC", [$query])
            ->get();

        return view('pages.fullTextSearchResults', ['users' => $users, 'query' => $query]);
    }
    */

    //Uses full text search for name and username and exact match search for email
    public function search(Request $request)
    {
        if (!Auth::check()) {
            $query = trim($request->input('q'));

            if (str_contains($query, '@')) {
                // Use the local scope for public profiles
                $users = User::publicProfile()
                            ->orWhere('email', 'like', '%' . $request->input('q') . '%')
                            ->get();
                $viewName = 'pages.search';
            } else {
                // Use the local scope for public profiles in full-text search
                $users = User::publicProfile()
                            ->whereRaw("tsvectors @@ plainto_tsquery('english', ?)", [$query])
                            ->orderByRaw("ts_rank(tsvectors, plainto_tsquery('english', ?)) DESC", [$query])
                            ->get();
                $viewName = 'pages.search';
            }

            return view($viewName, ['users' => $users, 'query' => $query]);
        } else {
            $query = trim($request->input('q'));
            $currentUser = Auth::user();

            // Fetch public users
            $publicUsers = User::where('public_profile', true)
                            ->where(function ($query) use ($request) {
                                $query->where('name', 'ILIKE', $request->input('q') . '%')
                                        ->orWhere('username', 'ILIKE', $request->input('q') . '%')
                                        ->orWhere('email', 'ILIKE', $request->input('q') . '%')
                                        ->orWhereRaw("tsvectors @@ plainto_tsquery('english', ?)", [$request->input('q')])
                                        ->orderByRaw("ts_rank(tsvectors, plainto_tsquery('english', ?)) DESC", [$request->input('q')]);

                            })
                            ->get();

            // Fetch friends of the current user
            $friends = $currentUser->get_friends_helper()->where(function ($query) use ($request) {
                                                            $query->where('name', 'ILIKE', $request->input('q') . '%')
                                                                    ->orWhere('username', 'ILIKE', '%' . $request->input('q') . '%')
                                                                    ->orWhere('email', 'ILIKE', '%' . $request->input('q') . '%')
                                                                    ->orWhereRaw("tsvectors @@ plainto_tsquery('english', ?)", [$request->input('q')])
                                                                    ->orderByRaw("ts_rank(tsvectors, plainto_tsquery('english', ?)) DESC", [$request->input('q')]);
                                                                })
                                                                ->get();
            // Combine and remove duplicates
            $users = $publicUsers->merge($friends)->unique('id');

            return view('pages.search', ['users' => $users, 'query' => $query]);
        }

        
    }

}