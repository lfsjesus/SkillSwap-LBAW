<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Administrator extends Model
{
    use HasFactory;
    public $timestamps  = false;
    protected $table='administrators';

    protected $fillable = [
        'name',
        'username',
        'email',
        'password'
    ];

}
