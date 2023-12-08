<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Friend extends Model
{
    use HasFactory;
    public $timestamps  = false;
    protected $table='is_friend';

    protected $fillable = [
        'user_id',
        'friend_id',
        'date'
    ];

    protected $primaryKey = [
        'user_id',
        'friend_id'
    ];

    public $incrementing = false;
}
