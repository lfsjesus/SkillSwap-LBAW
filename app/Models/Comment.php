<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function likes() {
        return $this->hasMany(Like::class, 'comment_id');
    }

    public function getLikesCount() {
        return $this->likes()->count();
    }

    public function isLikedBy($user_id) {
        return $this->likes()->where('user_id', $user_id)->count() > 0;
    }
}
