<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Notification;
use App\Models\CommentNotification;

class CommentController extends Controller
{
    public function createComment(Request $request) {
        $request->validate([
            'content' => 'required',
            'post_id' => 'required'
        ]);

        $post_id = $request->input('post_id');
        $replyTo_id = $request->input('comment_id') ?? null;
        $content = $request->input('content');

        $post = Post::find($post_id);

        if ($post == null) {
            return json_encode(['success' => false, 'error' => 'Post not found!']);
        }    

        if ($replyTo_id != null) {
            $replyTo = Comment::find($replyTo_id);
            if ($replyTo == null) {
                return json_encode(['success' => false, 'error' => 'Comment not found!']);
            }
        }

        $this->authorize('createComment', Comment::class);

        try {
            DB::beginTransaction();
            $comment = new Comment();
            $comment->user_id = Auth::user()->id;
            $comment->post_id = $post_id;
            $comment->comment_id = $replyTo_id;
            $comment->content = nl2br($content);
            $comment->date = date('Y-m-d H:i:s');
            $comment->save();            

            $notification = new Notification();
            $notification->sender_id = Auth::user()->id;
            $notification->receiver_id = $comment->post->user_id;
            $notification->date = date('Y-m-d H:i:s');

            $notification->save();

            $commentNotification = new CommentNotification();
            $commentNotification->notification_id = $notification->id;
            $commentNotification->comment_id = $comment->id;
            $commentNotification->notification_type = 'new_comment';

            $commentNotification->save();

            DB::commit();

            $response = array(
                'success' => true,
                'id' => $comment->id,
                'post_id' => $post_id,
                'replyTo_id' => $replyTo_id,
                'content' => $comment->content,
                'author_name' => Auth::user()->name
            );

            return json_encode($response);
        } catch (\Exception $e) {
            DB::rollback();
            return json_encode(['success' => false, 'error' => 'Unexpected error while creating comment. Try again!']);
        }
    }

    public function deleteComment(Request $request) {
        $request->validate([
            'id' => 'required'
        ]);

        $comment = Comment::find($request->input('id'));

        if ($comment == null) {
            return json_encode(['success' => false, 'error' => 'Comment not found!']);
        }

        $this->authorize('deleteComment', $comment);

        try {
            DB::beginTransaction();

            $comment->delete();
            
            // Database trigger deletes all notifications

            DB::commit();

            $response = array(
                'success' => true,
                'id' => $comment->id,
            );

            return json_encode($response);
        } catch (\Exception $e) {
            DB::rollback();
            return json_encode(['success' => false, 'error' => 'Unexpected error while deleting comment. Try again!']);
        }
    }

    public function editComment(Request $request) {
        $request->validate([
            'id' => 'required',
            'content' => 'required'
        ]);

        $id = $request->input('id');
        $content = $request->input('content');

        $comment = Comment::find($id);

        if ($comment == null) {
            return json_encode(['success' => false, 'error' => 'Comment not found!']);
        }

        $this->authorize('editComment', $comment);

        try {
            DB::beginTransaction();

            $comment->content = nl2br($content);
            $comment->save();
            DB::commit();

            $response = array(
                'success' => true,
                'id' => $id,
                'post_id' => $comment->post_id,
                'replyTo_id' => $comment->comment_id,
                'content' => $comment->content,
                'author_name' => $comment->author->name
            );

            return json_encode($response);
        } catch (\Exception $e) {
            DB::rollback();
            return json_encode(['success' => false, 'error' => 'Unexpected error while editing comment. Try again!']);
        }
    }
}
