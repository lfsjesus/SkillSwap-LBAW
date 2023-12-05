<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function get_members()
    {
        return $this->belongsToMany(User::class, Group::class, 'group_user', 'group_id', 'user_id')->get();
    }

}
