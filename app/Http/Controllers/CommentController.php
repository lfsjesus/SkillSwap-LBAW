<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Models\Comment;

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
            DB::commit();

            $response = array(
                'id' => $comment->id,
                'post_id' => $post_id,
                'replyTo_id' => $replyTo_id,
                'content' => $comment->content,
                'author_name' => Auth::user()->name,
                'author_id' => Auth::user()->id
            );

            return json_encode($response);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError('Unexpected error while creating comment. Try again!');
        }
    }

    public function deleteComment(Request $request) {
        $comment_id = $request->input('comment_id');

        if (!isset($comment_id)) {
            return redirect()->back()->withError('Unexpected error while deleting comment. Try again!');
        }

        try {
            DB::beginTransaction();
            $comment = Comment::find($comment_id);
            $comment->delete();
            DB::commit();

            $response = array(
                'comment_id' => $comment_id
            );

            return json_encode($response);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError('Unexpected error while deleting comment. Try again!');
        }
    }
}
