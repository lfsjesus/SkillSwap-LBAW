<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;


class Administrator extends Authenticatable
{
    use HasFactory;
    public $timestamps  = false;

    protected $table='administrators';

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'remember_token'
    ];

}