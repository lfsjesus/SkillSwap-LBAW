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
        'birth_date'
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

    /**
     * Get the friends for a user.
     */
    public function get_friends():Collection
    {
        return $this->belongsToMany(User::class, Friend::Class, 'user_id', 'friend_id')->get();
    }

    /**
     * Get the groups for a user.
     */
    public function get_groups():Collection
    {
        return $this->belongsToMany(Group::class, Member::Class, 'user_id', 'group_id')->get();
    }

}
