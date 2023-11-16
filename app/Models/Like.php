<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;
    public $timestamps  = false;
    protected $table='likes';

    protected $fillable = [
        'user_id',
        'post_id',
        'comment_id',
        'date'
    ];
}
