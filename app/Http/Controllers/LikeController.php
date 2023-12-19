<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Models\Like;
use App\Models\Notification;
use App\Models\PostNotification;
use App\Models\CommentNotification;
use App\Models\Post;
use App\Models\Comment;

class LikeController extends Controller
{
    public function likePost(Request $request) {
        $request->validate([
            'post_id' => 'required',
        ]);

        $post_id = $request->input('post_id');
        $post = Post::find($post_id);
        $user_id = Auth::user()->id;

        if ($post == null) {
            return json_encode(['success' => false, 'error' => 'Post not found!']);
        }

        $this->authorize('likePost', [Like::class, $post]);

        try {
            DB::beginTransaction();
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

            DB::commit();

            $response = array(
                'success' => true,
                'post_id' => $post_id,
                'liked' => $liked
            );

            return json_encode($response);
        } catch (\Exception $e) {
            DB::rollback();
            return json_encode(['success' => false, 'error' => 'Unexpected error while liking post. Try again!']);
        }
    }

    public function likeComment(Request $request) {
        $request->validate([
            'comment_id' => 'required',
        ]);

        $comment_id = $request->input('comment_id');
        $comment = Comment::find($comment_id);
        $user_id = Auth::user()->id;

        if ($comment == null) {
            return json_encode(['success' => false, 'error' => 'Comment not found!']);
        }

        $this->authorize('likeComment', [Like::class, $comment]);

        try {
            DB::beginTransaction();
            $like = Like::where('user_id', $user_id)->where('comment_id', $comment_id)->first();

            if ($like) {
                $like->delete();
                $liked = false;

                $notification_join = Notification::join('comment_notifications', 'notifications.id', '=', 'comment_notifications.notification_id')
                                            ->where('notifications.sender_id', $user_id)
                                            ->where('notifications.receiver_id', $comment->user_id)
                                            ->where('comment_notifications.notification_type', 'like_comment')
                                            ->firstOrFail();

                $notification_id = $notification_join->id;
                $notification_sender = $notification_join->sender_id;
                $notification = Notification::find($notification_id);
                
                $notification->delete();
            } else {
                $like = new Like();
                $like->user_id = $user_id;
                $like->comment_id = $comment_id;
                $like->date = date('Y-m-d H:i:s');
                $like->save();
                $liked = true;

                $notification = new Notification();
                $notification->sender_id = $user_id;
                $notification->receiver_id = $comment->user_id;
                $notification->date = date('Y-m-d H:i:s');

                $notification->save();

                $commentNotification = new CommentNotification();
                $commentNotification->notification_id = $notification->id;
                $commentNotification->comment_id = $comment_id;
                $commentNotification->notification_type = 'like_comment';

                $commentNotification->save();
            }
            
            DB::commit();

            $response = array(
                'success' => true,
                'comment_id' => $comment_id,
                'liked' => $liked
            );

            return json_encode($response);
            
        } catch (\Exception $e) {
            DB::rollback();
            return json_encode(['success' => false, 'error' => 'Unexpected error while liking comment. Try again!']);
        }
    }
}
