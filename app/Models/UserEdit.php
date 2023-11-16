<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEdit extends Model
{
    use HasFactory;
    public $timestamps  = false;
    protected $table='user_edit';

    protected $fillable = [
        'user_id',
        'administrator_id',
        'date',
        'field_type'
    ];

}
