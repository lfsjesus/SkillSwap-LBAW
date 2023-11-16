<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBlock extends Model
{
    use HasFactory;
    public $timestamps  = false;
    protected $table='user_blocks';

    protected $fillable = [
        'blocked_by',
        'blocked_user'
    ];
}
