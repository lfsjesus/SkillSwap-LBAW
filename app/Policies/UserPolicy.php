<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

class UserPolicy
{
    use HandlesAuthorization;

    public function show(Authenticatable|null $user): bool
    {
        return true;
    }

    public function showEditForm(User $user, User $user2): bool
    {
        if (Auth::check()) {
            return $user->id === $user2->id;
        }
        return false;
    }

    public function edit(User $user, User $user2): bool
    {
        if (Auth::check()) {
            return $user->id === $user2->id;
        }
        return false;
    }

    public function userDelete(User $user, User $user2): bool
    {
        if (Auth::check()) {
            return $user->id === $user2->id;
        }
        return false;
    }

    public function sendFriendRequest(User $user, User $user2): bool
    {
        // Database manages uniqueness of friend requests or
        // already existing friendships.
        if (Auth::check()) {
            return $user->id !== $user2->id;
        }
        return false;
    }

    public function cancelFriendRequest(User $user, User $user2): bool
    {
        if (Auth::check()) {
            if ($user->sentFriendRequestTo($user2)) {
                return true;
            }
        }
        return false;
    }

    public function acceptFriendRequest(User $user, User $user2): bool
    {
        if (Auth::check()) {
            if ($user2->sentFriendRequestTo($user)) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function rejectFriendRequest(User $user, User $user2): bool
    {
        if (Auth::check()) {
            if ($user2->sentFriendRequestTo($user)) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function removeFriend(User $user, User $user2): bool
    {
        if (Auth::check()) {
            if ($user->isFriendWith($user2)) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function showFriends(Authenticatable|null $user, User $user2): bool
    {
        if ($user2->isPublic()) {
            return true;
        }

        if (Auth::guard('webadmin')->check()) {
            return true;
        }
        else if (Auth::check()) {
            if ($user->id === $user2->id) {
                return true;
            }
            else {
                return $user->isFriendWith($user2);
            }
        }
        return false;
    }

    public function showGroups(Authenticatable|null $user, User $user2): bool
    {
        if ($user2->isPublic()) {
            return true;
        }

        if (Auth::guard('webadmin')->check()) {
            return true;
        }

        else if (Auth::check()) {
            if ($user->id === $user2->id) {
                return true;
            }
            else {
                return $user->isFriendWith($user2);
            }
        }
        return false;
    }
}
