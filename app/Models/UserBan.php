<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBan extends Model
{
    use HasFactory;
    public $timestamps  = false;
    protected $table='user_ban';

    protected $fillable = [
        'user_id',
        'administrator_id',
        'date'
    ];

}
