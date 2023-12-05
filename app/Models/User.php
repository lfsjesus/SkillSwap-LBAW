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
        'remember_token'
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

    public function isFriendWith($userId): bool
    {
        return $this->friends()->where('friend_id', $userId)->exists();
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


    public function scopePublicProfile($query)
    {
        return $query->where('public_profile', true);
    }

    /**
     * Get the friends for a user to be used on full text search.
     */
    public function get_friends_helper()
    {
        return $this->belongsToMany(User::class, Friend::class, 'user_id', 'friend_id');
    }

    public function is_friend($user)
    {
        return $this->friends()->where('friend_id', $user->id)->exists();
    }

    // Assuming you have a friends relationship defined like this
    public function friends()
    {
        return $this->belongsToMany(User::class, Friend::class, 'user_id', 'friend_id');
    }

    public function is_member($group)
    {
        return $this->groups()->where('group_id', $group->id)->exists();
    }

    public function is_owner($group)
    {
        return $this->owners()->where('group_id', $group->id)->exists();
    }
}
