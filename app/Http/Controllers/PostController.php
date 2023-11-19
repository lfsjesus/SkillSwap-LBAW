<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\Post;
use App\Models\Files;

class PostController extends Controller
{
    public function list() {
        if (Auth::check()) {
            $posts = Post::publicPosts()->get()->sortByDesc('date');
            return view('pages.home', ['posts' => $posts]);
        }
    }

    public function create(Request $request) {

        // check for authorization

        $content = $request->input('description');

        if (!isset($content)) {
            return redirect()->back()->with('error', 'The post cannot be empty');
        }

        try {
            DB::beginTransaction();
            $post = new Post();
            $post->user_id = Auth::user()->id;
            $post->group_id = $request->input('group_id', null);
            $post->description = nl2br($content);
            $post->date = date('Y-m-d H:i:s');
            $post->public_post = $request->input('public_post', true);
            $post->save();

            FileController::uploadFiles($request, $post->id);

            DB::commit();

            return redirect()->back()->with('success', 'Post created successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error in creating post');
        }
    }

    public function delete(Request $request) {
        $post_id = $request->input('post_id');

        if (!isset($post_id)) {
            return redirect()->back()->with('error', 'Post not found');
        }

        try {
            DB::beginTransaction();
            $post = Post::find($post_id);
            $files = $post->files();
            
            if ($post->user_id != Auth::user()->id) {
                return redirect()->back()->with('error', 'You are not authorized to delete this post');
            }

            $post->delete();
            
            FileController::deleteFilesFromStorage($files);

            DB::commit();
            return redirect()->back()->with('success', 'Post deleted successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error in deleting post');
        }
    }

}