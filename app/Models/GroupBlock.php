<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupBlock extends Model
{
    use HasFactory;
    public $timestamps  = false;
    protected $table='group_blocks';

    protected $fillable = [
        'group_id',
        'blocked_user',
        'blocked_by',
        'date'
    ];
}
