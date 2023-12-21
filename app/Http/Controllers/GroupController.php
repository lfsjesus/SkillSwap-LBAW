<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Models\Group;
use App\Models\Member;
use App\Models\GroupOwner;
use App\Models\Notification;
use App\Models\GroupNotification;
use App\Models\User;


class GroupController extends Controller 
{
    public function show(int $id) {
        $group = Group::where('id', $id)->firstOrFail();
        
        $this->authorize('show', Group::class);

        return view('pages.group', ['group' => $group]);
    }

    public function showCreateForm() {
        $this->authorize('showCreateForm', Group::class);

        return view('pages.createGroup');
    }

    public function showEditForm($id) {
        $group = Group::where('id', $id)->firstOrFail();

        $this->authorize('showEditForm', $group);

        return view('pages.editGroup', ['group' => $group]);
    }

    public function list()
    {
        $groups = DB::table('groups')->simplePaginate(20);
        return view('pages.groups', ['groups' => $groups]); 
    }


    public function create(Request $request) {
        $request->validate([
            'name' => 'required|string|max:50',
            'description' => 'required|string|max:255',
            'visibility' => 'required|boolean',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120'
        ]);

        $this->authorize('create', Group::class);

        try {
            DB::beginTransaction();

            $group = new Group();
            $group->name = $request->name;
            $group->banner = ($request->file('banner') != null) ? 'data:image/png;base64,' . base64_encode(file_get_contents($request->file('banner'))) : null;
            $group->description = $request->description;
            $group->public_group = $request->visibility;
            $group->date = date('Y-m-d H:i:s');

            $group->save();

            // Add user as member
            $groupMember = new Member();
            $groupMember->user_id = Auth::user()->id;
            $groupMember->group_id = $group->id;
            $groupMember->date = date('Y-m-d H:i:s');
            $groupMember->save();
            

            // Add user as owner 
            $groupOwner = new GroupOwner();
            $groupOwner->user_id = Auth::user()->id;
            $groupOwner->group_id = $group->id;
            $groupOwner->date = date('Y-m-d H:i:s');
            $groupOwner->save();

            DB::commit();

            return redirect()->route('group', ['id' => $group->id])->with('success', 'Group created successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('groups')->with('error', 'Group creation failed');
        }

    }

    public function edit(Request $request) {
        $request->validate([
            'id' => 'required|integer',
            'name' => 'required|string|max:50',
            'description' => 'required|string|max:255',
            'visibility' => 'required|boolean',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120'
        ]);
        
        $group = Group::find($request->id);
        
        if ($group == null) {
            return redirect()->route('groups')->with('error', 'Group not found');
        }

        $this->authorize('edit', $group);

        try {
            DB::beginTransaction();

            $group->name = $request->name;
            $group->banner = ($request->file('banner') != null) ? 'data:image/png;base64,' . base64_encode(file_get_contents($request->file('banner'))) : null;
            $group->description = $request->description;
            $group->public_group = $request->visibility;
            $group->date = date('Y-m-d H:i:s');

            $group->save();

            DB::commit();

            if (Auth::check('webadmin')) {
                return redirect()->route('view-group-admin', ['id' => $group->id])->with('success', 'Group edited successfully');
            }

            return redirect()->route('group', ['id' => $group->id])->with('success', 'Group edited successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('groups')->with('error', 'Group edit failed');
        }
    }

    public function deleteGroup(Request $request) {
        $request->validate([
            'id' => 'required|integer'
        ]);

        $group = Group::find($request->id);

        if ($group == null) {
            return redirect()->route('groups')->with('error', 'Group not found');
        }

        $this->authorize('delete', $group);

        $group->delete();

        return redirect()->route('groups')->with('success', 'Group deleted successfully');
    }

    public function showMembers($groupId)
    {
        $group = Group::where('id', $groupId)->firstOrFail();
        
        $this->authorize('showMembers', $group);

        return view('pages.group_members', ['group' => $group]);
    }

    public function showOwners($groupId)
    {
        $group = Group::findOrFail($groupId);

        return view('pages.group_owners', ['group' => $group]);
    }

    public function sendJoinGroupRequest(Request $request)
    {   
        $group = Group::find($request->input('group_id'));

        if ($group == null) {
            return json_encode(['success' => false, 'error' => 'Group not found']);
        }

        $this->authorize('sendJoinGroupRequest', $group);

        try {
            DB::beginTransaction();
            //save each new notification_id in an array

            $notifications_ids = [];

            foreach ($group->owners()->get() as $owner) {

                $notification = new Notification();
                $notification->sender_id = Auth::user()->id;
                $notification->receiver_id = $owner->id;
                $notification->date = date('Y-m-d H:i:s');
                $notification->save();

                array_push($notifications_ids, $notification->id);           

                $groupNotification = new GroupNotification();
                $groupNotification->notification_id = $notification->id;
                $groupNotification->group_id = $group->id;
                $groupNotification->notification_type = 'join_request';
                $groupNotification->save();
            }

            DB::commit();
            //return json_encode sucess with the array of notifications

            return json_encode(['success' => true, 'notifications_ids' => $notifications_ids]);

            
        } catch (\Exception $e) {
            DB::rollback();
            return json_encode(['success' => false, 'error' => 'Unexpected error while sending join group request. Try again!']);
        }
    }

    public function cancelJoinGroupRequest(Request $request)
    {
        $group = Group::find($request->input('group_id'));

        if ($group == null) {
            return json_encode(['success' => false, 'error' => 'Group not found']);
        }

        $this->authorize('cancelJoinGroupRequest', $group);

        try {
            DB::beginTransaction();

            $notifications = Notification::join('group_notifications', 'notifications.id', '=', 'group_notifications.notification_id')
                        ->where('notifications.sender_id', Auth::user()->id)
                        ->where('group_notifications.group_id', $group->id)
                        ->where('group_notifications.notification_type', 'join_request')
                        ->get();

            foreach ($notifications as $notification) {
                
                //find the notification in the notification table and delete it
                $real_notification = Notification::find($notification->id);
                $real_notification->delete();
            }

            DB::commit();
            return json_encode(['success' => true]);
        } catch (\Exception $e) {
            DB::rollback();
            return json_encode(['success' => false, 'error' => 'Unexpected error while canceling join group request. Try again!']);
        }
    }

    public function acceptJoinGroupRequest(Request $request)
    {
        $request->validate([
            'group_id' => 'required|integer',
            'sender_id' => 'required|integer'
        ]);

        $group = Group::find($request->input('group_id'));
        $sender_id = $request->input('sender_id');
        $sender = User::find($sender_id);

        if ($group == null) {
            return json_encode(['success' => false, 'error' => 'Group not found']);
        }

        if ($sender == null) {
            return json_encode(['success' => false, 'error' => 'User not found']);
        }

        $this->authorize('acceptJoinGroupRequest', [Group::class,$sender, $group]);

        try {
            DB::beginTransaction();
            
            //find every notification that has the sender_id and the group_id
            
            $notifications = Notification::join('group_notifications', 'notifications.id', '=', 'group_notifications.notification_id')
                        ->where('notifications.sender_id', $sender_id)
                        ->where('group_notifications.group_id', $group->id)
                        ->where('group_notifications.notification_type', 'join_request')
                        ->get();


            foreach ($notifications as $notification) {

                //find the notification in the notification table and delete it
                $real_notification = Notification::find($notification->id);
                $notification_id = $real_notification->id;
                $real_notification->delete();
            }
            
            $member = new Member();
            $member->user_id = $sender_id;
            $member->group_id = $group->id;
            $member->date = date('Y-m-d H:i:s');
            $member->save();

            DB::commit();
            return json_encode(['success' => true, 'notification_id' => $notification_id]);
        } catch (\Exception $e) {
            DB::rollback();
            return json_encode(['success' => false, 'error' => 'Unexpected error while accepting join group request. Try again!']);
        }
    }

    public function rejectJoinGroupRequest(Request $request)
    {
        $request->validate([
            'group_id' => 'required|integer',
            'sender_id' => 'required|integer'
        ]);

        $group = Group::find($request->input('group_id'));
        $sender_id = $request->input('sender_id');
        $sender = User::find($sender_id);

        if ($group == null) {
            return json_encode(['success' => false, 'error' => 'Group not found']);
        }

        if ($sender == null) {
            return json_encode(['success' => false, 'error' => 'User not found']);
        }

        $this->authorize('rejectJoinGroupRequest', [Group::class, $sender, $group]);

        try {
            DB::beginTransaction();
            
            //find every notification that has the sender_id and the group_id
            
            $notifications = Notification::join('group_notifications', 'notifications.id', '=', 'group_notifications.notification_id')
                        ->where('notifications.sender_id', $sender_id)
                        ->where('group_notifications.group_id', $group->id)
                        ->where('group_notifications.notification_type', 'join_request')
                        ->get();
                        
            foreach ($notifications as $notification) {
                    
                    //find the notification in the notification table and delete it
                    $real_notification = Notification::find($notification->id);
                    $notification_id = $real_notification->id;
                    $real_notification->delete();
                }

            DB::commit();
            return json_encode(['success' => true,  'notification_id' => $notification_id]);
        } catch (\Exception $e) {
            DB::rollback();
            return json_encode(['success' => false, 'error' => 'Unexpected error while rejecting join group request. Try again!']);
        }
    }

    public function leaveGroup(Request $request) {
        $request->validate([
            'group_id' => 'required|integer'
        ]);

        $group = Group::find($request->group_id);

        if ($group == null) {
            return json_encode(['success' => false, 'error' => 'Group not found']);
        }
         
        $this->authorize('leaveGroup', $group);

        try {
            DB::beginTransaction();

            DB::table('is_member')
                ->where('user_id', Auth::user()->id)
                ->where('group_id', $group->id)
                ->delete();

            //also, if the user is an owner, delete him from the owners table
            DB::table('owns')
                ->where('user_id', Auth::user()->id)
                ->where('group_id', $group->id)
                ->delete();

            DB::commit();

            return json_encode(['success' => true]);
        } catch (\Exception $e) {
            DB::rollback();
            return json_encode(['success' => false, 'error' => 'Unexpected error while leaving group. Try again!']);
        }
    }

    public function addMember(Request $request) {
        $request->validate([
            'group_id' => 'required|integer',
            'user' => 'required'
        ]);

        $group = Group::find($request->group_id);

        if ($group == null) {
            return redirect()->back()->withErrors(['add_member' => 'Group not found']);
        }
        
        $this->authorize('addMember', $group);

        if (filter_var($request->user, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $request->user)->first();
        } else {
            $user = User::where('username', $request->user)->first();
        }

        if ($user == null) {
            return redirect()->back()->withErrors(['add_member' => 'User not found']);
        }

        if ($group->isMember($user)) {
            return redirect()->back()->withErrors(['add_member' => 'User is already a member of this group']);
        }

        try {
            DB::beginTransaction();

            $member = new Member();
            $member->user_id = $user->id;
            $member->group_id = $group->id;
            $member->date = date('Y-m-d H:i:s');
            $member->save();

            DB::commit();

            return redirect()->back()->withSuccess('Member added successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['add_member' => 'Unexpected error while adding member to group. Try again!']);
        }
    }

    public function removeMember(Request $request) {
        $request->validate([
            'group_id' => 'required|integer',
            'user_id' => 'required|integer'
        ]);

        $group = Group::find($request->group_id);
        $user = User::find($request->user_id);
        
        if ($group == null) {
            return json_encode(['success' => false, 'error' => 'Group not found']);
        }

        if ($user == null) {
            return json_encode(['success' => false, 'error' => 'User not found']);
        }

        $this->authorize('removeMember', $group);

        try {
            DB::beginTransaction();

            DB::table('is_member')
                ->where('user_id', $user->id)
                ->where('group_id', $group->id)
                ->delete();

            //also, if the user is an owner, delete him from the owners table
            DB::table('owns')
                ->where('user_id', $user->id)
                ->where('group_id', $group->id)
                ->delete();

            DB::commit();

            return json_encode(['success' => true, 'user_id' => $user->id]);
        } catch (\Exception $e) {
            DB::rollback();
            return json_encode(['success' => false, 'error' => 'Unexpected error while removing member from group. Try again!']);
        }
    }

    public function addOwner(Request $request) {
        $request->validate([
            'group_id' => 'required|integer',
            'user' => 'required'
        ]);

        $group = Group::find($request->group_id);
        if ($group == null) {
            return redirect()->back()->withErrors(['add_owner' => 'Group not found']);
        }


        $this->authorize('addOwner', $group);

        if (filter_var($request->user, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $request->user)->first();
        } else {
            $user = User::where('username', $request->user)->first();
        }

        if ($user == null) {
            return redirect()->back()->withErrors(['add_owner' => 'User not found']);
        }

        if ($group->isOwner($user)) {
            return redirect()->back()->withErrors(['add_owner' => 'User is already an owner of this group']);
        }

        try {
            DB::beginTransaction();

            $owner = new GroupOwner();
            $owner->user_id = $user->id;
            $owner->group_id = $group->id;
            $owner->date = date('Y-m-d H:i:s');
            $owner->save();
    
            if (!$group->isMember($user)) {
                $member = new Member();
                $member->user_id = $user->id;
                $member->group_id = $group->id;
                $member->date = date('Y-m-d H:i:s');
                $member->save();
            }

            DB::commit();

            return redirect()->back()->withSuccess('Owner added successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['add_owner' => 'Unexpected error while adding owner to group. Try again!']);
        }
    }   

    public function removeOwner(Request $request) {
        $request->validate([
            'group_id' => 'required|integer',
            'user_id' => 'required|integer'
        ]);

        $group = Group::find($request->group_id);
        $user = User::find($request->user_id);
        
        if ($group == null) {
            return json_encode(['success' => false, 'error' => 'Group not found']);
        }

        if ($user == null) {
            return json_encode(['success' => false, 'error' => 'User not found']);
        }

        $this->authorize('removeOwner', $group);

        try {
            DB::beginTransaction();

            DB::table('owns')
                ->where('user_id', $user->id)
                ->where('group_id', $group->id)
                ->delete();

            DB::commit();

            return json_encode(['success' => true, 'user_id' => $user->id]);
        } catch (\Exception $e) {
            DB::rollback();
            return json_encode(['success' => false, 'error' => 'Unexpected error while removing owner from group. Try again!']);
        }
    }
}
