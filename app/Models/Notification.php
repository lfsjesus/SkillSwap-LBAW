<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\UserNotification;
use App\Models\GroupNotification;
use App\Models\PostNotification;
use App\Models\CommentNotification;

class Notification extends Model
{
    use HasFactory;
    public $timestamps  = false;
    protected $table='notifications';

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'date',
        'viewed'
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function subNotification()
    {
        $userNotification = UserNotification::where('notification_id', $this->id)->first();
        $groupNotification = GroupNotification::where('notification_id', $this->id)->first();
        $postNotification = PostNotification::where('notification_id', $this->id)->first();
        $commentNotification = CommentNotification::where('notification_id', $this->id)->first();

        if($userNotification != null)
        {
            return $userNotification;
        }
        else if($groupNotification != null)
        {
            return $groupNotification;
        }
        else if($postNotification != null)
        {
            return $postNotification;
        }
        else if($commentNotification != null)
        {
            return $commentNotification;
        }

        return null;
    }

}
