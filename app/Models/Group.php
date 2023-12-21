<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Post;
use App\Models\User;
use App\Models\Member;

class Group extends Model
{
    use HasFactory;
    public $timestamps  = false;
    protected $table='groups';

    protected $fillable = [
        'name',
        'banner',
        'description',
        'public_group',
        'date'
    ];


    /**
    * Get the posts for a group.
    */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function publicPosts()
    {
        return $this->posts()->where('public_post', true);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, Member::class, 'group_id', 'user_id');
    }

    public function get_members()
    {
        return $this->members()->get();
    }

    public function owners()
    {
        return $this->belongsToMany(User::class, GroupOwner::class, 'group_id', 'user_id');
    }

    public function get_owners()
    {
        return $this->owners()->get();
    }

    public function isOwner($user)
    {
        return $this->owners()->where('user_id', $user->id)->exists();
    }

    public function isMember($user)
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    public function userHasSentJoinRequest($user) : bool
    {
        $userId = $user->id;
        return Notification::join('group_notifications', 'notifications.id', '=', 'group_notifications.notification_id')
                       ->where('notifications.sender_id', $userId)
                       ->where('group_notifications.group_id', $this->id)
                       ->where('group_notifications.notification_type', 'join_request')
                       ->exists();

                    
    }

    public function calculatePopularity() {
        $members = $this->get_members()->count();
        $posts = $this->posts()->count();
        $popularity = $members + $posts;

        return $popularity;
    }

    public function isPublic() {
        return $this->public_group;
    }

}
