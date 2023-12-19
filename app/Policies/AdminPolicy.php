<?php

namespace App\Policies;

use App\Models\Administrator;
use App\Models\User;
use App\Models\Group;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;


class AdminPolicy
{
    use HandlesAuthorization;

    public function showUser() : bool {
        return Auth::guard('webadmin')->check();
    }

    public function showCreateUserForm() : bool {
        return Auth::guard('webadmin')->check();
    }

    public function showEditUserForm() : bool {
        return Auth::guard('webadmin')->check();
    }

    public function showGroup() : bool {
        return Auth::guard('webadmin')->check();
    }

    public function showEditGroupForm() : bool {
        return Auth::guard('webadmin')->check();
    }

    public function createUser() : bool {
        return Auth::guard('webadmin')->check();
    }

    public function editUser() : bool {
        return Auth::guard('webadmin')->check();
    }

    public function deleteUser() : bool {
        return Auth::guard('webadmin')->check();
    }

    public function banUser() : bool {
        return Auth::guard('webadmin')->check();
    }

    public function unbanUser() : bool {
        return Auth::guard('webadmin')->check();
    }

}
