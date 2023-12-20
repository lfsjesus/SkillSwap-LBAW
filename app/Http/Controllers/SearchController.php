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

    public function search(Request $request) {
        $query = $request->input('q');
        $type = $request->input('type', 'user'); // Default to 'users
        $dateSort = $request->input('date', 'desc'); // Default to 'desc'
        $popularitySort = $request->input('popularity', 'desc'); // Default to 'desc'

        $results = collect();

        if ($type == 'post') {
            $results = $this->posts($request);
        }

        else if ($type == 'user') {
            $results = $this->users($request);
        }

        else if ($type == 'group') {
            $results = $this->groups($request);
        }

        else if ($type == 'comment') {
            $results = $this->comments($request);
        }

        if ($dateSort == 'asc') {
            $results = $results->sortBy('date');
        }

        else if ($dateSort == 'desc') {
            $results = $results->sortByDesc('date');
        }

        if ($popularitySort == 'asc') {
            $results = $results->sortBy(function($item) {
                return $item->calculatePopularity();
            });
        }
            
        else if ($popularitySort == 'desc') {
            $results = $results->sortByDesc(function($item) {
                return $item->calculatePopularity();
            });
        }

        if ($request->ajax()) {
            return response()->json($results);
        }

        else {
            return view('pages.search', ['results' => $results, 
                                        'query' => $query, 
                                        'type' => $type, 
                                        'date' => $dateSort, 
                                        'popularity' => $popularitySort]);
        }
    }

    protected function posts(Request $request) {
        $query = $request->input('q');

        if (Auth::guard('webadmin')->check()) {
            $posts = Post::WhereRaw("tsvectors @@ plainto_tsquery('english', ?)", [$request->input('q')])
                            ->orWhere('description', '=', $request->input('q'))
                            ->get();
        }

        else if (Auth::user()) {
            $posts = Post::WhereRaw("tsvectors @@ plainto_tsquery('english', ?)", [$request->input('q')])
                            ->WhereIn('id', Auth::user()->visiblePosts()->pluck('id'))
                            ->orWhere('description', '=', $request->input('q'))
                            ->get();
                                
        }
        else {
            $posts = Post::publicPosts()
                            ->WhereRaw("tsvectors @@ plainto_tsquery('english', ?)", [$request->input('q')])
                            ->orWhere('description', '=', $request->input('q'))
                            ->get(); 
        }

        return $posts;
    }

    protected function users(Request $request) {
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

    protected function groups(Request $request) {
        $query = $request->input('q');

        $groups = Group::whereRaw("tsvectors @@ plainto_tsquery('english', ?)", [$request->input('q')])
                    ->orderByRaw("ts_rank(tsvectors, plainto_tsquery('english', ?)) DESC", [$request->input('q')])
                    ->orWhere('description', '=', $request->input('q'))
                    ->orWhere('name', '=', $request->input('q'))
                    ->get();

        return $groups;
    }


    protected function comments(Request $request) {
        $query = $request->input('q');

        if (Auth::guard('webadmin')->check()) {
            $comments = Comment::WhereRaw("tsvectors @@ plainto_tsquery('english', ?)", [$request->input('q')])
                                ->orWhere('content', '=', $request->input('q'))
                    ->get();
        }

        else if (Auth::user()) {            
            $comments = Comment::WhereRaw("tsvectors @@ plainto_tsquery('english', ?)", [$request->input('q')])
                            ->WhereIn('id', Auth::user()->visibleComments()->pluck('id'))
                            ->orWhere('content', '=', $request->input('q'))
                            ->get();
        }
        else {
            $comments = Comment::publicComments()
                    ->WhereRaw("tsvectors @@ plainto_tsquery('english', ?)", [$request->input('q')])
                    ->orWhere('content', '=', $request->input('q'))
                    ->get();
        }
                    
        return $comments;
    }
}

?>