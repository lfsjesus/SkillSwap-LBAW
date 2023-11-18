<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;
    public $timestamps  = false;
    protected $table='files';


    protected $fillable = [
        'post_id',
        'comment_id',
        'title',
        'file_path',
        'date'
    ];

    public function post() {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function comment() {
        return $this->belongsTo(Comment::class, 'comment_id');
    }

    
    
}
