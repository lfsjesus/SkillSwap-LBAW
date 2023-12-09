<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Models\Like;
use App\Models\Notification;
use App\Models\PostNotification;
use App\Models\Post;

class LikeController extends Controller
{
    public function likePost(Request $request) {
        $post_id = $request->input('post_id');
        $post = Post::find($post_id);
        $user_id = Auth::user()->id;

        if (!isset($post_id)) {
            return redirect()->back()->withError('Unexpected error while liking post. Try again!');
        }

        $like = Like::where('user_id', $user_id)->where('post_id', $post_id)->first();

        if ($like) {
            $like->delete();
            $liked = false;

            $notification_join = Notification::join('post_notifications', 'notifications.id', '=', 'post_notifications.notification_id')
                                        ->where('notifications.sender_id', $user_id)
                                        ->where('notifications.receiver_id', $post->user_id)
                                        ->where('post_notifications.notification_type', 'like_post')
                                        ->firstOrFail();
            
            $notification_id = $notification_join->id;
            $notification_sender = $notification_join->sender_id;

            $notification = Notification::find($notification_id);

            $notification->delete();

        } else {
            $like = new Like();
            $like->user_id = $user_id;
            $like->post_id = $post_id;
            $like->date = date('Y-m-d H:i:s');
            $like->save();
            $liked = true;

            $notification = new Notification();
            $notification->sender_id = $user_id;
            $notification->receiver_id = $post->user_id;
            $notification->date = date('Y-m-d H:i:s');
    
            $notification->save();
    
            $postNotification = new PostNotification();
            $postNotification->notification_id = $notification->id;
            $postNotification->post_id = $post_id;
            $postNotification->notification_type = 'like_post';
    
            $postNotification->save();
        }


        $response = array(
            'post_id' => $post_id,
            'liked' => $liked
        );

        return json_encode($response);
    }

    public function likeComment(Request $request) {
        $comment_id = $request->input('comment_id');
        $user_id = Auth::user()->id;

        if (!isset($comment_id)) {
            return redirect()->back()->withError('Unexpected error while liking comment. Try again!');
        }

        $like = Like::where('user_id', $user_id)->where('comment_id', $comment_id)->first();

        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            $like = new Like();
            $like->user_id = $user_id;
            $like->comment_id = $comment_id;
            $like->date = date('Y-m-d H:i:s');
            $like->save();
            $liked = true;
        }

        $response = array(
            'comment_id' => $comment_id,
            'liked' => $liked
        );
        return json_encode($response);
    }
}
