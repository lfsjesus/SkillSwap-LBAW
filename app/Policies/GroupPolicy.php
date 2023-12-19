<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use App\Models\Group;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;

class GroupPolicy
{
    use HandlesAuthorization;

    public function show(User|null $user) : bool {
        return true;
    }

    public function showCreateForm() : bool {
        return Auth::check();
    }

    public function showEditForm(User $user, Group $group) : bool {
        if (Auth::check()) {
            if ($group->isOwner($user)) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function create() : bool {
        return Auth::check();
    }

    public function edit(Authenticatable|null $user, Group $group) : bool {
        if (Auth::guard('webadmin')->check()) {
            return true;
        }      
        else if (Auth::check()) {
            return $group->isOwner($user);
        }
        return false;
    }    

    public function delete(Authenticatable|null $user, Group $group) : bool {
        if (Auth::guard('webadmin')->check()) {
            return true;
        }      
        else if (Auth::check()) {
            return $group->isOwner($user);
        }
        return false;
    }

    public function showMembers(Authenticatable|null $user, Group $group) : bool {
        if ($group->public_group) {
            return true;
        }

        if (Auth::guard('webadmin')->check()) {
            return true;
        }

        else if (Auth::check()) {
            if ($group->isMember($user)) {
                return true;
            }
            return false;
        }

        return false;
    }

    public function showOwners(Authenticatable|null $user, Group $group) : bool {
        if ($user2->public_group) {
            return true;
        }

        if (Auth::guard('webadmin')->check()) {
            return true;
        }

        else if (Auth::check()) {
            if ($group->isMember($user)) {
                return true;
            }
            return false;
        }

        return false;
    }

    public function sendJoinGroupRequest(User $user, Group $group) : bool {
        if (Auth::check()) {
            if (!$group->isMember($user)) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function cancelJoinGroupRequest(User $user, Group $group) : bool {
        if (Auth::check()) {
            if (!$group->isMember($user)) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function acceptJoinGroupRequest(User $user, Group $group) : bool {
        return Auth::check();
    }

    public function rejectJoinGroupRequest(User $user, Group $group) : bool {
        return Auth::check();
    }

    public function addMember(User $user, Group $group) : bool {
        if (Auth::check()) {
            if ($group->isOwner($user)) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function leaveGroup(User $user, Group $group) : bool {
        if (Auth::check()) {
            if ($group->isMember($user)) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function removeMember(User $user, Group $group) {
        if (Auth::check()) {
            if ($group->isOwner($user)) {
                return true;
            }
            return false;
        }
        return false;
    }

}


