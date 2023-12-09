<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostNotification extends Model
{
    use HasFactory;
    public $timestamps  = false;
    protected $table='post_notifications';

    protected $fillable = [
        'post_id',
        'notification_type'
    ];

    public $incrementing = false;

    public function notification()
    {
        return $this->belongsTo(Notification::class, 'notification_id');
    }
}
