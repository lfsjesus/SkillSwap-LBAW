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

    public $incrementing = false;

    public function group() {
        return $this->belongsTo(Group::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function isMember($user, $group)
    {
        return $this->group()->members()->where('user_id', $user->id)->where('group_id', $group->id)->exists();
    }
}
