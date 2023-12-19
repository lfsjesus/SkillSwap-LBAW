<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Comment;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;


class CommentPolicy
{
    use HandlesAuthorization;

    public function createComment() : bool {
        // Database trigger checks other conditions
        // that determine if the comment is valid
        // (e.g. if the post is visible to the user,
        // etc.)
        return Auth::check();
    }

    public function deleteComment(Authenticatable $user, Comment $comment) : bool {
        if (Auth::guard('webadmin')->check()) {
            return true;
        }
        else if (Auth::check()) {
            return $user->id === $comment->user_id;
        }
        return false;
    }

    public function editComment(Authenticatable $user, Comment $comment) : bool {
        if (Auth::guard('webadmin')->check()) {
            return true;
        }
        else if (Auth::check()) {
            return $user->id === $comment->user_id;
        }
        return false;
    }
}
