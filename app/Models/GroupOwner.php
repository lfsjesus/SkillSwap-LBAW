<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupOwner extends Model
{
    use HasFactory;
    public $timestamps  = false;
    protected $table='owns';

    protected $fillable = [
        'user_id',
        'group_id',
        'date'
    ];

}
