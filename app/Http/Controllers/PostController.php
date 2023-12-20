<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\Post;
use App\Models\File;

class PostController extends Controller
{
    public function show($id) {
        $post = Post::where('id', $id)->firstOrFail();

        $this->authorize('show', $post);

        return view('pages.post', ['post' => $post]);
    }
    
    public function list() {
        if (Auth::check()) {
            $posts = Auth::user()->visiblePosts()->simplePaginate(10);
            return view('pages.home', ['posts' => $posts]);
        }

        $posts = Post::publicPosts()->orderBy('date','desc')->simplePaginate(10);
        return view('pages.home', ['posts' => $posts]);
    }

    public function create(Request $request) {
        $this->authorize('create', Post::class);

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
            $post->public_post = $request->input('visibility', false);
            
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
        $post = Post::find($request->input('post_id'));

        if ($post == null) {
            return json_encode(['success' => false, 'error' => 'Post not found']);
        }

        $this->authorize('delete', $post);

        try {
            DB::beginTransaction();

            $files = $post->files();
            
            $post->delete();
            
            FileController::deleteFilesFromStorage($files);

            DB::commit();

            $response = [
                'success' => true,
                'id' => $post->id
            ];

            return json_encode($response);
        } catch (\Exception $e) {
            DB::rollback();
            return json_encode(['success' => false, 'error' => 'Unexpected error while deleting post. Try again!']);
        }
    }

    public function edit(Request $request) {
        $request->validate([
            'description' => 'required',
            'files.*' => 'nullable|mimes:jpg,jpeg,png,gif,doc,docx,pdf,txt|max:10240'
        ]);

        $post_id = $request->input('post_id');
        $content = $request->input('description');

        $post = Post::find($post_id);

        if ($post == null) {
            return redirect()->back()->with('error', 'Post not found');
        }

        $this->authorize('edit', $post);

        try {
            DB::beginTransaction();
            
            $postFiles = $post->files();

            $requestFilesNames = ($request->file('files') != null) ? array_map(function ($file) {
                return $file->getClientOriginalName();
            }, $request->file('files')) : [];


            $postFilesNames = array_map(function ($file) {
                return $file['title'];
            }, $postFiles->toArray());
            

            $post->description = nl2br($content);
            $post->public_post = $request->input('visibility', false);
            
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