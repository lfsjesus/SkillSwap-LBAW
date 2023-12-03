<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Models\Like;

class LikeController extends Controller
{
    public function likePost(Request $request) {
        $post_id = $request->input('post_id');
        $user_id = Auth::user()->id;

        if (!isset($post_id)) {
            return redirect()->back()->withError('Unexpected error while liking post. Try again!');
        }

        $like = Like::where('user_id', $user_id)->where('post_id', $post_id)->first();

        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            $like = new Like();
            $like->user_id = $user_id;
            $like->post_id = $post_id;
            $like->date = date('Y-m-d H:i:s');
            $like->save();
            $liked = true;
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
