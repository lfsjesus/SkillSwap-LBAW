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
}
