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


class GroupController extends Model 
{
    public function show(int $id) {
        $group = Group::find($id);
        $posts = $group->posts()->get();
        return view('pages.group', ['group' => $group]);
    }

    public function showCreateForm() {
        if (!Auth::check()) {
            return redirect()->route('home')->with('error', 'You cannot create a group');
        }

        return view('pages.createGroup');
    }

    public function showEditForm($id) {
        if (!Auth::check()) {
            return redirect()->route('home')->with('error', 'You cannot edit a group');
        }

        $group = Group::find($id);

        if (!$group->is_owner(Auth::user())) {
            return redirect()->route('home')->with('error', 'You cannot edit a group you do not own');
        }

        return view('pages.editGroup', ['group' => $group]);
    }

    public function list()
    {
        $groups = DB::table('groups')->simplePaginate(20);
        return view('pages.groups', ['groups' => $groups]); 
    }


    public function create(Request $request) {
        if (!Auth::check()) {
            return redirect()->route('home')->with('error', 'You cannot create a group');
        }

        $request->validate([
            'name' => 'required|string|max:50',
            'description' => 'required|string|max:255',
            'visibility' => 'required|boolean',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

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
            'name' => 'required|string|max:50',
            'description' => 'required|string|max:255',
            'visibility' => 'required|boolean',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        try {
            DB::beginTransaction();

            $group = Group::find($request->id);
            $group->name = $request->name;
            $group->banner = ($request->file('banner') != null) ? 'data:image/png;base64,' . base64_encode(file_get_contents($request->file('banner'))) : null;
            $group->description = $request->description;
            $group->public_group = $request->visibility;
            $group->date = date('Y-m-d H:i:s');

            $group->save();

            DB::commit();

            return redirect()->route('group', ['id' => $group->id])->with('success', 'Group edited successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('groups')->with('error', 'Group edit failed');
        }
    }

    public function deleteGroup(Request $request) {
        $group = Group::find($request->id);

        if (!$group->is_owner(Auth::user())) {
            return redirect()->route('home')->with('error', 'You cannot delete a group you do not own');
        }

        $group->delete();

        return redirect()->route('groups')->with('success', 'Group deleted successfully');
    }

    public function showMembers($groupId)
    {
        $group = Group::findOrFail($groupId);
        $members = Member::where('group_id', $groupId)->get(); 

        return view('pages.group_members', ['group' => $group, 'members' => $members]);
    }

    public function showOwners($groupId)
    {
        $group = Group::findOrFail($groupId);
        $owners = $group->owners()->get(); 

        return view('pages.group_owners', ['group' => $group, 'owners' => $owners]);
    }

    public function sendJoinGroupRequest(Request $request)
    {   
        if(!Auth::check()) {
            return redirect()->back()->with('error', 'You must be logged in to join a group');
        }

        $group = Group::find($request->input('group_id'));

        if ($group->is_member(Auth::user())) {
            return redirect()->back()->with('error', 'You are already a member of this group');
        }

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
            return redirect()->back()->with('error', 'Unexpected error while sending join group request. Try again!');
        }
    }
}