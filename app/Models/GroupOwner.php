<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupOwner extends Model
{
    use HasFactory;
    public $timestamps  = false;
    protected $table='owns';

    protected $primaryKey = [
        'user_id',
        'group_id'
    ];

    public $incrementing = false;

    public function group() {
        return $this->belongsTo(Group::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function isOwner($user, $group)
    {
        return $this->group()->owners()->where('user_id', $user->id)->where('group_id', $group->id)->exists();
    }
}
