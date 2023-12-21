<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\GroupController;

use App\Models\Administrator;
use App\Models\User;
use Illuminate\Support\Facades\Redis;
use App\Models\UserBan;
use App\Models\Group;
use App\Models\Friend;
use App\Models\Member;
use App\Models\Notification;

class AdminController extends Controller
{
    public function show() {
        if (Auth::guard('webadmin')->check()) {
            $username = Auth::guard('webadmin')->user()->username;
            $admin = Administrator::where('username', $username)->firstOrFail();
            $users = DB::table('users')->simplePaginate(20);
            return view('pages.admin', ['admin' => $admin, 'users' => $users]);
        }
        return redirect('/admin/login');
    }

    public function listGroups() {
        if (Auth::guard('webadmin')->check()) {
            $groups = DB::table('groups')->simplePaginate(20);
            return view('pages.groups', ['groups' => $groups]);  
        }
        return redirect('/admin/login');              
    }

    public function showUser($username) {
        $this->authorize('showUser', Administrator::class);

        $user = User::where('username', $username)->firstOrFail();
        $posts = $user->posts()->get();
        return view('pages.view-user-admin', ['user' => $user, 'posts' => $posts]);
    }


    public function showEditUserForm($username) {
        $this->authorize('showEditUserForm', Administrator::class);
        
        $user = User::where('username', $username)->firstOrFail();
        return view('pages.editProfile', ['user' => $user]);
    }

    public function showCreateUserForm() {
        $this->authorize('showCreateUserForm', Administrator::class);

        return view('pages.create-user-admin');
    }

    public function showGroup($id) {
        $this->authorize('showGroup', Administrator::class);

        $group = Group::where('id', $id)->firstOrFail();
        $posts = $group->posts()->get();
        return view('pages.view-group-admin', ['group' => $group, 'posts' => $posts]);
    }

    public function showEditGroupForm($id) {
        $this->authorize('showEditGroupForm', Administrator::class);

        $group = Group::where('id', $id)->firstOrFail();
        return view('pages.editGroup', ['group' => $group]);
    }

    public function createUser(Request $request) {
        $this->authorize('createUser', Administrator::class);

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
            'birth_date' => 'required|date|before:18 years ago',
            'password' => 'required|min:8|confirmed'
        ]);

        try {
            DB::beginTransaction();
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

            DB::commit();
        return redirect()->route('view-user-admin', ['username' => $user->username])->withSuccess('User created successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('create-user-form-admin')->withError('Unexpected error occurred while creating user!');
        }
    }

    public function editUser(Request $request) {
        $this->authorize('editUser', Administrator::class);

        $id = $request->input('user_id');

        $user = User::find($id);

        if ($user == null) {
            return redirect()->back()->with('error', 'User not found!');
        }

        // perform validation
        $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|email|max:50|unique:users,email,' . $id,
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
                'unique:users,username,' . $id,
                'not_regex:/^deleted/'
            ],
            'birth_date' => 'required|date|before:18 years ago',
            'visibility' => 'required|boolean',
            'password' => 'nullable|required_with:old_password|min:8|confirmed'
        ]
        , $customMessages = [
            'username.not_regex' => 'Username can\'t start with \'deleted\''
        ]);

        try {
            DB::beginTransaction();
            $user->name = $request->input('name');
            $user->username = $request->input('username');
            $user->email = $request->input('email');
            $user->phone_number = ($request->input('phone_number') != null) ? $request->input('phone_number') : $user->phone_number;
            $user->birth_date = date('Y-m-d', strtotime($request->input('birth_date')));
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

            return redirect()->route('view-user-admin', ['username' => $user->username])->withSuccess('Profile updated successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Unexpected error occurred while updating profile!');
        }
    }

    public function deleteUser(Request $request) {
        $this->authorize('deleteUser', Administrator::class);

        $id = $request->input('id');
        $user = User::find($id);

        if ($user == null) {
            return redirect()->back()->with('error', 'User not found!');
        }

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
        return redirect()->route('admin')->withSuccess('User deleted successfully!');
    }

    public function banUser(Request $request) {
        $this->authorize('banUser', Administrator::class);
        
        $username = $request->input('username');
        $user = User::where('username', $username)->first();

        if ($user == null) {
            return json_encode(['success' => false, 'error' => 'User not found!']);
        }

        try {
            DB::beginTransaction();

            $userBan = new UserBan();
            $userBan->user_id = $user->id;
            $userBan->administrator_id = Auth::guard('webadmin')->user()->id;
            $userBan->date = date('Y-m-d H:i:s');

            $userBan->save();

            $response = [
                'success' => true,
                'username' => $user->username
            ];

            DB::commit();
            return json_encode($response);
        }
        catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'success' => false,
                'error' => 'Unexpected error occurred while banning user!'

            ];

            return json_encode($response);
        }
    }


    public function unbanUser(Request $request) {
        $this->authorize('unbanUser', Administrator::class);

        $username = $request->input('username');
        $user = User::where('username', $username)->first();

        if ($user == null) {
            return json_encode(['success' => false, 'error' => 'User not found!']);
        }

        try {
            DB::beginTransaction();

            $id = $request->input('username');
            $user = User::where('username', $id)->firstOrFail();

            $userBan = UserBan::where('user_id', $user->id)->firstOrFail();

            $userBan->delete();

            $response = [
                'success' => true,
                'username' => $user->username
            ];

            DB::commit();
            return json_encode($response);
        }
        catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'success' => false,
                'error' => 'Unexpected error occurred while unbanning user!'
            ];

            return json_encode($response);
        }
    }
}
