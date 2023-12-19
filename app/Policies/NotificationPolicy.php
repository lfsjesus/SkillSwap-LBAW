<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;


class NotificationPolicy
{
    use HandlesAuthorization;

    public function markAsRead(User $user, Notification $notification) : bool {
        if (Auth::check()) {
            return $notification->receiver_id == $user->id;
        }
        return false;
    }

    public function markAllAsRead(User $user) : bool {
        if (Auth::check()) {
            return true;
        }
        return false;
    }
}
