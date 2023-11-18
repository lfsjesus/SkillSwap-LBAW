<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;
    public $timestamps  = false;
    protected $table='is_member';

    protected $fillable = [
        'user_id',
        'group_id',
        'date'
    ];

    protected $primaryKey = [
        'user_id',
        'group_id'
    ];
}
