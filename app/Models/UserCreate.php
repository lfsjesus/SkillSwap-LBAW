<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCreate extends Model
{
    use HasFactory;
    public $timestamps  = false;
    protected $table='user_create';

    protected $fillable = [
        'user_id',
        'administrator_id',
        'date',
        'name',
        'username',
        'email',
        'password',
        'birth_date',
        'public_profile'
    ];
}
