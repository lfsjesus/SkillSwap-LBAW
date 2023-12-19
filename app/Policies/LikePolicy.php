<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Like;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;


class LikePolicy
{
    use HandlesAuthorization;

    public function likePost(User $user, Post $post): bool
    {
        // Database checks other conditions for like
        // Such as being part of the 
        if (Auth::check()) {
            return true;
        }
        return false;
    }

    public function likeComment(User $user, Comment $comment): bool
    {
        if (Auth::check()) {
            // Check if comment is visible to user. DB does not check this.
            if ($user->visibleComments()
                        ->where('comments.id', $comment->id)
                        ->first()) {
                return true;
            }
        }
        return false;
    }

}
