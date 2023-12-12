<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Notification;

class UserNotification extends Model
{
    use HasFactory;
    public $timestamps  = false;
    protected $table='user_notifications';

    protected $fillable = [
        'notification_type'
    ];

    public $incrementing = false;

    public function notification()
    {
        return $this->belongsTo(Notification::class, 'notification_id');

    }
}
