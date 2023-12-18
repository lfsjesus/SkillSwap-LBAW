<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
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
                'id' => $comment->id,
                'post_id' => $post_id,
                'replyTo_id' => $replyTo_id,
                'content' => $comment->content,
                'author_name' => Auth::user()->name
            );

            return json_encode($response);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError('Unexpected error while creating comment. Try again!');
        }
    }

    public function deleteComment(Request $request) {
        $id = $request->input('id');

        if (!isset($id)) {
            return redirect()->back()->withError('Unexpected error while deleting comment. Try again!');
        }

        $comment = Comment::find($id);

        try {
            DB::beginTransaction();

            if (!(Auth::guard('webadmin')->check() || $comment->user_id == Auth::user()->id)) {
                return redirect()->back()->withError('You are not authorized to delete this comment!');
            }
            
            $comment->delete();
            
            // There is a trigger that removes notification.

            DB::commit();

            $response = array(
                'id' => $id
            );

            return json_encode($response);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError('Unexpected error while deleting comment. Try again!');
        }
    }

    public function editComment(Request $request) {
        $request->validate([
            'id' => 'required',
            'content' => 'required'
        ]);

        $id = $request->input('id');
        $content = $request->input('content');

        try {
            DB::beginTransaction();
            $comment = Comment::find($id);

            if (!(Auth::guard('webadmin')->check() || $comment->user_id == Auth::user()->id)) {
                return redirect()->back()->withError('You are not authorized to edit this comment!');
            }

            $comment->content = nl2br($content);
            $comment->save();
            DB::commit();

            $response = array(
                'id' => $id,
                'post_id' => $comment->post_id,
                'replyTo_id' => $comment->comment_id,
                'content' => $comment->content,
                'author_name' => $comment->author->name
            );

            return json_encode($response);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError('Unexpected error while editing comment. Try again!');
        }
    }
}
