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

    public function members()
    {
        return $this->belongsToMany(User::class, Member::class, 'group_id', 'user_id');
    }

    public function get_members()
    {
        return $this->members()->get();
    }


}
