<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Collection;

// Added to define Eloquent relationships.
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\Post;
use App\Models\Group;
use App\Models\Friend;
use App\Models\Member;
use App\Models\UserBan;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Don't add create and update timestamps in database.
    public $timestamps  = false;

    protected $table = 'users';


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'phone_number',
        'profile_picture',
        'description',
        'birth_date',
        'remember_token',
        'public_profile',
        'deleted'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
        'birth_date' => 'datetime'
    ];

    /**
     * Get the posts for a user.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function publicPosts()
    {
        return $this->hasMany(Post::class)->where('public_post', true);
    }

    public function visiblePosts() {
        $myPosts = $this->posts()->select('posts.*');
        
        // Make sure that private group posts from friends don't show
        $friendsPosts = $this->friendsPosts()->where('group_id', null)->select('posts.*');
    
        $groupsIamMember = $this->groups()->join('posts', 'posts.group_id', '=', 'groups.id')->select('posts.*');
    
        $publicPosts = Post::publicPosts()->select('posts.*');
    
        $posts = $myPosts->union($friendsPosts)->union($publicPosts)->union($groupsIamMember)->distinct();
        
        $posts = $posts->orderBy('date', 'desc');

        return $posts;
    }
    

    public function visibleComments() { // Can see comments on posts that are visible to me
        $visiblePosts = $this->visiblePosts()->pluck('id');

        return Comment::whereIn('post_id', $visiblePosts);
    }

    public function friendsPosts()
    {
        return $this->hasManyThrough(Post::class, Friend::class, 'user_id', 'user_id', 'id', 'friend_id');
    }

    /**
     * Get the friends for a user.
     */
    public function get_friends():Collection
    {
        return $this->belongsToMany(User::class, Friend::class, 'user_id', 'friend_id')->get();
    }

    public function isFriendWith(User $user): bool {
        return $this->friends()->where('friend_id', $user->id)->exists();
    }

    /**
     * Get the groups for a user.
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class, Member::class, 'user_id', 'group_id');
    }

    public function get_groups()
    {
        return $this->groups()->get();
    }


    public function scopeActiveUsers($query)
    {
        return $query->where('deleted', false);
    }

    /**
     * Get the friends for a user to be used on full text search.
     */
    public function get_friends_helper()
    {
        return $this->belongsToMany(User::class, Friend::class, 'user_id', 'friend_id');
    }

    public function friends()
    {
        return $this->belongsToMany(User::class, Friend::class, 'user_id', 'friend_id');
    }

    public function sentFriendRequestTo($user): bool
    {
        $userId = $user instanceof User ? $user->id : $user;

        return Notification::join('user_notifications', 'notifications.id', '=', 'user_notifications.notification_id')
                       ->where('notifications.sender_id', $this->id)
                       ->where('notifications.receiver_id', $userId)
                       ->where('user_notifications.notification_type', 'friend_request')
                       ->exists();
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'receiver_id');
    }

    public function isBanned() {
        return UserBan::where('user_id', $this->id)->exists();
    }

    public function hasUnreadNotifications() {
        return $this->notifications()->where('viewed', false)->exists();
    }

    public function calculatePopularity() {
        $popularity = $this->get_friends()->count();

        return $popularity;
    }

    public function isPublic() {
        return $this->public_profile;
    }

}

