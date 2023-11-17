<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    public $timestamps  = false;
    protected $table='posts';

    protected $casts = [
        'public_post' => 'boolean',
        'date' => 'datetime'
    ];

    protected $fillable = [
        'user_id',
        'group_id',
        'date',
        'description',
        'public_post'
    ];

    public static function publicPosts() {
        return Post::where('public_post', true);
    }

    public function author() {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function group() {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function comments() {
        return $this->hasMany(Comment::class);
    }

    public function likes() {
        return count($this->hasMany(Like::class)->get());
    }

    public function files() {
        return $this->hasMany(File::class);
    }

}