<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupNotification extends Model
{
    use HasFactory;
    public $timestamps  = false;
    protected $table='group_notifications';

    protected $fillable = [
        'group_id',
        'notification_type'
    ];

    public $incrementing = false;

    public function notification()
    {
        return $this->belongsTo(Notification::class, 'notification_id');
    }
}
