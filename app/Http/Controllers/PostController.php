<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\Post;
use App\Models\File;

class PostController extends Controller
{
    public function list() {
        if (!Auth::check()) {
            $posts = Post::publicPosts()->get()->sortByDesc('date');
            return view('pages.home', ['posts' => $posts]);
        }

        /*Else if user is logged in, show public posts + friends posts + own user posts */
        $posts = Post::publicPosts()->get()
            ->merge(Auth::user()->posts()->get())
            ->merge(Auth::user()->friendsPosts()->get())->unique('id')
            ->sortByDesc('date');
        return view('pages.home', ['posts' => $posts]);

    }

    public function create(Request $request) {
        $request->validate([
            'description' => 'required',
            'files.*' => 'nullable|mimes:jpg,jpeg,png,gif,doc,docx,pdf,txt|max:10240'
        ]);


        try {
            DB::beginTransaction();
            $post = new Post();
            $post->user_id = Auth::user()->id;
            $post->group_id = $request->input('group_id', null);
            $post->description = nl2br($request->input('description'));
            $post->date = date('Y-m-d H:i:s');
            $post->public_post = $request->input('public_post', true);
            
            $post->save();

            FileController::uploadFiles($request, $post->id);

            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError('Unexpected error while creating post. Try again!');
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

    public function edit(Request $request) {

        $post_id = $request->input('post_id');
        $content = $request->input('description');

        
        if (!isset($post_id)) {
            return redirect()->back()->with('error', 'Post not found');
        }

        if (!isset($content)) {
            return redirect()->back()->with('error', 'The post cannot be empty');
        }

        try {
            DB::beginTransaction();
            
            $post = Post::find($post_id);
            $postFiles = $post->files();

            $requestFilesNames = ($request->file('files') != null) ? array_map(function ($file) {
                return $file->getClientOriginalName();
            }, $request->file('files')) : [];


            $postFilesNames = array_map(function ($file) {
                return $file['title'];
            }, $postFiles->toArray());
            

            $post->description = nl2br($content);

            $toDelete = array_diff($postFilesNames, $requestFilesNames);
            
            $toDeleteFromDB = [];

            foreach ($toDelete as $filename) {
                $toDeleteFromDB[] = File::where('title', $filename)->where('post_id', $post_id)->firstOrFail();
            }
            
           
            FileController::deleteFilesFromStorage($toDeleteFromDB);

            foreach ($toDeleteFromDB as $file) {
                $file->delete();
            }
 
                
            $post->save();
        
            FileController::uploadFiles($request, $post_id);

            DB::commit();
            return redirect()->back()->with('success', 'Post edited successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error in editing post');
        }
    }

}