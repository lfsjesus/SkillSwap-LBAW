<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

use function PHPSTORM_META\map;

class Comment extends Model
{
    use HasFactory;
    public $timestamps  = false;
    protected $table='comments';

    protected $fillable = [
        'user_id',
        'post_id',
        'comment_id',
        'content',
        'date'
    ];

    public function author() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function post() {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function likes() {
        return $this->hasMany(Like::class, 'comment_id');
    }

    public function getLikesCount() {
        return $this->likes()->count();
    }

    public function isLikedBy($user_id) {
        return $this->likes()->where('user_id', $user_id)->count() > 0;
    }

    public function replies() {
        return $this->hasMany(Comment::class, 'comment_id');
    }

    public function getRepliesCount() {
        return $this->replies()->count();
    }

    public static function publicComments() { 
        $publicComments = Comment::whereHas('post', function($query) {
            $query->where('public_post', true);
        });

        return $publicComments;
    }

    public function isParent() {
        return $this->comment_id == null;
    }

    // get all comments that are descendants of this comment (not only direct replies)
    public function descendants() {
        $descendants = collect();
        $replies = $this->replies;

        foreach ($replies as $reply) {
            $descendants->push($reply);
            $descendants = $descendants->merge($reply->descendants());   
        }
    
        return $descendants->sortBy('date');
    }
    
    public function calculatePopularity() {
        $likes = $this->getLikesCount();
        $replies = $this->getRepliesCount();

        $popularity = $likes + $replies;

        return $popularity;
    }   
}
