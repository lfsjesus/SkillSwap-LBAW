<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\Post;

class PostController extends Controller
{
    public function list() {
        if (Auth::check()) {
            $posts = Post::publicPosts()->get();
            return view('pages.home', ['posts' => $posts]);
        }
    }


}