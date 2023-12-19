<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Post;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;

class PostPolicy
{
    use HandlesAuthorization;

    public function show($user, Post $post): bool
    {
        if ($post->public_post) {
            return true;
        }

        if (Auth::guard('webadmin')->check()) {
            return true;
        }

        else if (Auth::check()) {
            if ($post->author->id === Auth::user()->id) {
                return true;
            }
            else {
                return Auth::user()->isFriendWith($post->author);
            }
        }
        return false;
    }

    public function create(): bool
    {
        return Auth::check();
    }

    public function delete($user, Post $post): bool
    {
        if (Auth::guard('webadmin')->check()) {
            return true;
        }      
        else if (Auth::check()) {
            return $post->author->id === Auth::user()->id;
        }
        return false;
    }

    public function edit($user, Post $post): bool
    {
        if (Auth::guard('webadmin')->check()) {
            return true;
        }      
        else if (Auth::check()) {
            return $post->author->id === Auth::user()->id;
        }
        return false;
    }

    
}
