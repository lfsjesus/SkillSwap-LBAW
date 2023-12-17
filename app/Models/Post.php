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

    public function directComments() {
        return $this->comments()->whereNull('comment_id');
    }

    public function getCommentsCount() {
        return $this->comments()->count();
    }

    public function likes() {
        return $this->hasMany(Like::class)->get();
    }

    public function getLikesCount() {
        return $this->likes()->count();
    }

    public function isLikedBy($user_id) {
        return $this->likes()->where('user_id', $user_id)->count() > 0;
    }

    public function files() {
        return $this->hasMany(File::class)->get();
    }

    public function calculatePopularity() {
        $likes = $this->getLikesCount();
        $comments = $this->getCommentsCount();
        $popularity = $likes + $comments;
        return $popularity;
    }
}