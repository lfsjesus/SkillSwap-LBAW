<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\Post;
use App\Models\User;
use App\Models\Group;
use App\Models\Comment;

class SearchController extends Controller {

    protected function posts(Request $request) {
        $query = $request->input('q');

        if (!(Auth::guard('webadmin')->check() && Auth::check())) {
            $posts = Post::publicPosts()
                    ->WhereRaw("tsvectors @@ plainto_tsquery('english', ?)", [$request->input('q')])
                    ->orderByRaw("ts_rank(tsvectors, plainto_tsquery('english', ?)) DESC", [$request->input('q')])
                    ->get();
        }

        else if (Auth::guard('webadmin')->check()) {
            $posts = Post::WhereRaw("tsvectors @@ plainto_tsquery('english', ?)", [$request->input('q')])
                    ->orderByRaw("ts_rank(tsvectors, plainto_tsquery('english', ?)) DESC", [$request->input('q')])
                    ->get();
        }

        else {
            $posts = Auth::user()->visiblePosts()
                    ->WhereRaw("tsvectors @@ plainto_tsquery('english', ?)", [$request->input('q')])
                    ->orderByRaw("ts_rank(tsvectors, plainto_tsquery('english', ?)) DESC", [$request->input('q')])
                    ->get();
        }

        return $posts;
    }

    public function users(Request $request) {
        $query = $request->input('q');

        $users = User::activeUsers()
                    ->where(function($query) use ($request) {
                        $query->Where('username', '=', $request->input('q'))
                                ->orWhere('email', '=', $request->input('q'))
                                ->orWhereRaw("tsvectors @@ plainto_tsquery('english', ?)", [$request->input('q')])
                                ->orderByRaw("ts_rank(tsvectors, plainto_tsquery('english', ?)) DESC", [$request->input('q')]);

                    })
                    ->get();

        return $users;
    }

    public function groups(Request $request) {

    }


    public function comments(Request $request) {
        
    }
}

?>