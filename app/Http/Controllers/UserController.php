<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Notification;
use App\Models\UserNotification;

class UserController extends Model 
{
    public function show(string $username) {
        if(!Auth::check()) {
            if(User::where('username', $username)->firstOrFail()->public_profile == false) {
                return redirect()->route('home')->with('error', 'You cannot view this profile');
            }
            $user = User::where('username', $username)->firstOrFail();
            $posts = $user->posts()->get();
            $groups = $user->get_groups();
            return view('pages.user', ['user' => $user, 'posts' => $posts, 'groups' => $groups]);
        }
        $user = User::where('username', $username)->firstOrFail();
        $posts = $user->posts()->get();
        $groups = $user->get_groups();
        return view('pages.user', ['user' => $user, 'posts' => $posts, 'groups' => $groups]);

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

        
        $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|email|max:50|unique:users,email,' . Auth::user()->id,
            'phone_number' => [
                'nullable',
                'regex:/^\+?\d+$/',
                'digits_between:8,15'
            ],
            'description' => 'nullable|string|max:500',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'username' => 'required|string|max:50|unique:users,username,' . Auth::user()->id,
            'birth_date' => 'required|date|before:18 years ago',
            'visibility' => 'required|boolean'
        ]);
        

        $user->name = $request->input('name');
        $user->username = $request->input('username');
        $user->email = $request->input('email');
        $user->phone_number = ($request->input('phone_number') != null) ? $request->input('phone_number') : $user->phone_number;
        $user->birth_date = $request->input('birth_date');
        $user->profile_picture = ($request->file('profile_picture') != null) ? 'data:image/png;base64,' . base64_encode(file_get_contents($request->file('profile_picture'))) : $user->profile_picture;
        $user->description = $request->input('description');
        $user->public_profile = $request->input('visibility');


        $user->save();
        return redirect()->route('user', ['username' => $user->username])->withSuccess('Profile updated successfully!');
    }

    public function userDelete(Request $request) { 
        if (!Auth::check()) {
            return redirect()->route('home')->with('error', 'You cannot delete this user');
        }

        $user = User::find($request->input('id'));

        if (Auth::user()->id != $user->id) {
            return redirect()->back()->with('error', 'You cannot delete this user');
        }

        $user->delete();

        Auth::logout();
        
        return redirect()->route('home')->with('success', 'User deleted successfully!');
    }

    public function sendFriendRequest(Request $request) {
        if (!Auth::check()) {
            return redirect()->route('home')->with('error', 'You cannot send a friend request');
        }
        

        $user = User::find($request->input('friend_id'));
        

        if (Auth::user()->id == $user->id) {
            return redirect()->back()->with('error', 'You cannot send a friend request to yourself');
        }

        // WE DONT NEED TO CHECK EXISTING NOTIFICATION. TRIGGER DOES THAT.
        // PROBABLY THE SAME WITH ALREADY FRIENDS (if trigger is working)
        // WE CAN ADD THE BLOCKED CONDITION TO THE TRIGGER
        
        /*
        if (Auth::user()->is_friends_with($user)) {
            return redirect()->back()->with('error', 'You are already friends with this user');
        }


        if (Auth::user()->has_blocked($user) || $user->has_blocked(Auth::user())) {
            return redirect()->back()->with('error', 'You cannot send a friend request to this user');
        }

        */
        try {
            DB::beginTransaction();

            $notification = new Notification();
            
            $notification->sender_id = Auth::user()->id;
            $notification->receiver_id = $user->id;
            $notification->date = date('Y-m-d H:i:s');
            
            $notification->save();
            
            $friendRequest = new UserNotification();
            
            $friendRequest->notification_id = $notification->id;
            $friendRequest->notification_type = 'friend_request';
            
            $friendRequest->save();

            DB::commit();

            return json_encode(['success' => true]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Unexpected error while sending friend request. Try again!');
        }

    }


    public function cancelFriendRequest(Request $request) {
        if (!Auth::check()) {
            return redirect()->route('home')->with('error', 'You cannot cancel a friend request');
        }

        $user = User::find($request->input('friend_id'));

        if (Auth::user()->id == $user->id) {
            return redirect()->back()->with('error', 'You cannot cancel a friend request to yourself');
        }

        /*
        if (!Auth::user()->has_pending_friend_request_from($user)) {
            return redirect()->back()->with('error', 'You do not have a pending friend request from this user');
        }
        */

        try {
            DB::beginTransaction();

            $notification_join = Notification::join('user_notifications', 'notifications.id', '=', 'user_notifications.notification_id')
                                        ->where('notifications.sender_id', Auth::user()->id)
                                        ->where('notifications.receiver_id', $user->id)
                                        ->where('user_notifications.notification_type', 'friend_request')
                                        ->firstOrFail();

            $notification_id = $notification_join->id;

            //delete the notification
            $notification = Notification::find($notification_id);

            $notification->delete();

            
            DB::commit();

            return json_encode(['success' => true]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Unexpected error while cancelling friend request. Try again!');
        }
    }

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

    public function showFriends($username)
    {
        $user = User::where('username', $username)->firstOrFail();
        $friends = $user->friends();

        return view('pages.user_friends', ['user' => $user, 'friends' => $friends]);
    }

    public function showGroups($username)
    {
        $user = User::where('username', $username)->firstOrFail();
       

        return view('pages.user_groups', ['user' => $user]);
    }


}