<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Notification;
use App\Models\UserNotification;
use App\Models\Friend;
use App\Models\Member;


class UserController extends Controller 
{
    public function show(string $username) {
        $user = User::where('username', $username)->firstOrFail();

        if (Auth::guard('webadmin')->check()) {
            return redirect()->route('view-user-admin', ['username' => $user->username]);
        }

        $this->authorize('show', User::class);

        return view('pages.user', ['user' => $user]);
    }


    public function showEditForm($username) {

        $user = User::where('username', $username)->firstOrFail();

        $this->authorize('showEditForm', $user);

        return view('pages.editProfile', ['user' => $user]);
    }

    public function edit(Request $request) {
        $user = User::find(Auth::user()->id);
        
        $this->authorize('edit', $user);

        $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|email|max:50|unique:users,email,' . Auth::user()->id,
            'phone_number' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (!empty($value)) {
                        $cleanedValue = preg_replace('/[^0-9\+]/', '', $value);
                        if (strlen($cleanedValue) < 8 || strlen($cleanedValue) > 15) {
                            $fail('Phone value is invalid.');
                        }
                    }
                },
            ],            
            'description' => 'nullable|string|max:500',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'username' => [
                'required',
                'string',
                'max:50',
                'unique:users,username,' . Auth::user()->id,
                'not_regex:/^deleted/'
            ],
            'birth_date' => 'required|date|before:18 years ago',
            'visibility' => 'required|boolean',
            'password' => 'nullable|required_with:old_password|min:8|confirmed'
        ],
        $customMessages = [
            'username.not_regex' => 'Username can\'t start with \'deleted\''
        ]);

        try {
            DB::beginTransaction();
            $user->name = $request->input('name');
            $user->username = $request->input('username');
            $user->email = $request->input('email');
            $user->phone_number = ($request->input('phone_number') != null) ? $request->input('phone_number') : $user->phone_number;
            $user->birth_date = $request->input('birth_date');
            $user->profile_picture = ($request->file('profile_picture') != null) ? 'data:image/png;base64,' . base64_encode(file_get_contents($request->file('profile_picture'))) : $user->profile_picture;
            $user->description = $request->input('description');
            $user->public_profile = $request->input('visibility');
            if ($request->input('password') != null) {
                if (Hash::check($request->input('old_password'), $user->password)) {
                    $user->password = bcrypt($request->input('password'));
                } else {
                    return redirect()->back()->withErrors(['old_password' => 'Old password is incorrect']);
                }
            }

            $user->save();

            DB::commit();
            return redirect()->route('user', ['username' => $user->username])->withSuccess('Profile updated successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Unexpected error while updating profile. Try again!');
        }
    }

    public function userDelete(Request $request) { 
        $user = User::find($request->input('id'));

        $this->authorize('userDelete', $user);

        try {
            DB::beginTransaction();

            $user->name = 'deleted';
            $user->username = 'deleted' . $user->id;
            $user->email = 'deleted' . $user->id;
            $user->phone_number = null;
            $user->birth_date = date('Y-m-d H:i:s', 1);
            $user->profile_picture = null;
            $user->description = null;
            $user->public_profile = false;
            $user->password = 'deleted';
            $user->deleted = true;

            $user->save();

            // Delete all notifications
            Notification::where('sender_id', $user->id)
                            ->orWhere('receiver_id', $user->id)
                            ->delete();

            // Delete all friendships
            Friend::where('user_id', $user->id)
                            ->delete();

            // Delete all group memberships
            Member::where('user_id', $user->id)
                            ->delete();

            // Delete all group ownerships
            DB::table('owns')
                ->where('user_id', $user->id)
                ->delete();
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Unexpected error while deleting user. Try again!');
        }
        Auth::logout();
        
        return redirect()->route('home')->with('success', 'User deleted successfully!');
    }

    public function sendFriendRequest(Request $request) {
        $user = User::find($request->input('friend_id'));
        
        if ($user == null) {
            return json_encode(['success' => false, 'error' => 'User not found']);
        }

        if ($user->deleted) {
            return json_encode(['success' => false, 'error' => 'User has been deleted']);
        }

        $this->authorize('sendFriendRequest', $user);

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
            return json_encode(['success' => false, 'error' => 'Unexpected error while sending friend request. Try again!']);
        }

    }


    public function cancelFriendRequest(Request $request) {
        $user = User::find($request->input('friend_id'));

        if ($user == null) {
            return json_encode(['success' => false, 'error' => 'User not found']);
        }

        $this->authorize('cancelFriendRequest', $user);

        try {
            DB::beginTransaction();

            $notification_join = Notification::join('user_notifications', 'notifications.id', '=', 'user_notifications.notification_id')
                                        ->where('notifications.sender_id', Auth::user()->id)
                                        ->where('notifications.receiver_id', $user->id)
                                        ->where('user_notifications.notification_type', 'friend_request')
                                        ->firstOrFail();

            $notification_id = $notification_join->id;

            // Delete the notification
            $notification = Notification::find($notification_id);

            $notification->delete();

            
            DB::commit();

            return json_encode(['success' => true]);
        } catch (\Exception $e) {
            DB::rollback();
            return json_encode(['success' => false, 'error' => 'Unexpected error while cancelling friend request. Try again!']);
        }
    }

    public function acceptFriendRequest(Request $request) {
        $user = User::find($request->input('sender_id'));

        if ($user == null) {
            return json_encode(['success' => false, 'error' => 'User not found']);
        }

        $this->authorize('acceptFriendRequest', $user);

        try {
            DB::beginTransaction();
            $notification_join = Notification::join('user_notifications', 'notifications.id', '=', 'user_notifications.notification_id')
                                        ->where('notifications.sender_id', $user->id)
                                        ->where('notifications.receiver_id', Auth::user()->id)
                                        ->where('user_notifications.notification_type', 'friend_request')
                                        ->firstOrFail();

            $notification_id = $notification_join->id;

            //delete the notification
            $notification = Notification::find($notification_id);
            $notification_sender = $notification->sender_id;

            $notification->delete();            

            $friendId = $user->id;  //the id of the user that sent the friend request
            //add the friendship
            $is_friend = new Friend();
            

            $is_friend->user_id = Auth::user()->id;
            $is_friend->friend_id = $friendId;
            $is_friend->date = date('Y-m-d H:i:s');

            $is_friend->save();

            DB::commit();

            return json_encode(['success' => true, 'notification_id' => $notification_id, 'sender_id' => $notification_sender]);
        } catch (\Exception $e) {
            DB::rollback();
            return json_encode(['success' => false, 'error' => 'Unexpected error while accepting friend request. Try again!']);
        }
    }

    public function rejectFriendRequest(Request $request) {
        $user = User::find($request->input('sender_id'));

        if ($user == null) {
            return json_encode(['success' => false, 'error' => 'User not found']);
        }

        $this->authorize('rejectFriendRequest', $user);

        try {
            DB::beginTransaction();

            $notification_join = Notification::join('user_notifications', 'notifications.id', '=', 'user_notifications.notification_id')
                                        ->where('notifications.sender_id', $user->id)
                                        ->where('notifications.receiver_id', Auth::user()->id)
                                        ->where('user_notifications.notification_type', 'friend_request')
                                        ->firstOrFail();

            $notification_id = $notification_join->id;
            $notification_sender = $notification_join->sender_id;

            //delete the notification
            $notification = Notification::find($notification_id);

            $notification->delete();

            DB::commit();

            return json_encode(['success' => true, 'notification_id' => $notification_id, 'sender_id' => $notification_sender]);
        } catch (\Exception $e) {
            DB::rollback();
            return json_encode(['success' => false, 'error' => 'Unexpected error while rejecting friend request. Try again!']);
        }
    }

    public function removeFriend(Request $request){
        $user = User::find($request->input('friend_id'));
        
        if ($user == null) {
            return json_encode(['success' => false, 'error' => 'User not found']);
        }

        $this->authorize('removeFriend', $user);

        try {
            DB::beginTransaction();

            DB::table('is_friend')
                ->where('user_id', Auth::user()->id)
                ->where('friend_id', $user->id)
                ->delete();

            DB::commit();

            return json_encode(['success' => true]);

        } catch (\Exception $e) {
            DB::rollback();
            return json_encode(['success' => false, 'error' => 'Unexpected error while removing friend. Try again!']);
        }
    }

    public function showFriends($username)
    {
        $user = User::where('username', $username)->firstOrFail();

        $this->authorize('showFriends', $user);

        return view('pages.user_friends', ['user' => $user]);
    }

    public function showGroups($username)
    {
        $user = User::where('username', $username)->firstOrFail();

        $this->authorize('showGroups', $user);
       
        return view('pages.user_groups', ['user' => $user]);
    }


}