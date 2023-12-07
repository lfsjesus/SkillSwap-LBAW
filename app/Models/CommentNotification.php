<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentNotification extends Model
{
    use HasFactory;
    public $timestamps  = false;
    protected $table='comment_notifications';

    protected $fillable = [
        'comment_id',
        'notification_type'
    ];

    public $incrementing = false;
}
