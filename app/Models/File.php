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
        'files',
        'date'
    ];

    
}