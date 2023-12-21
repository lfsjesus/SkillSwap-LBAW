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
        $betweenDoubleQuotes = preg_match('/^".*"$/', $query);

        if (Auth::guard('webadmin')->check()) {
            if ($betweenDoubleQuotes) {
                $query = substr($query, 1, -1);
                $posts = Post::Where('description', 'ilike', '%' . substr($request->input('q'), 1, -1) . '%')
                            ->get();
            }
            else {
                $posts = Post::WhereRaw("tsvectors @@ plainto_tsquery('english', ?)", [$request->input('q')])
                            ->orWhere('description', '=', $request->input('q'))
                            ->get();
            }
        }

        else if (Auth::user()) {
            if ($betweenDoubleQuotes) {
                $query = substr($query, 1, -1);
                $posts = Post::Where('description', 'ilike', '%' . substr($request->input('q'), 1, -1) . '%')
                            ->whereIn('id', Auth::user()->visiblePosts()->pluck('id'))
                            ->get();
            }
            else {
                $posts = Post::Where(function ($query) use ($request) {
                            $query->WhereRaw("tsvectors @@ plainto_tsquery('english', ?)", [$request->input('q')])
                                  ->orWhere('description', '=', $request->input('q'));
                        })
                        ->whereIn('id', Auth::user()->visiblePosts()->pluck('id'))
                        ->get();
            }
        }
        
        else {
            if ($betweenDoubleQuotes) {
                $query = substr($query, 1, -1);
                $posts = Post::publicPosts()
                            ->Where('description', 'ilike', '%' . substr($request->input('q'), 1, -1) . '%')
                            ->get();
            }
            else {
                $posts = Post::publicPosts()
                                ->WhereRaw("tsvectors @@ plainto_tsquery('english', ?)", [$request->input('q')])
                                ->orWhere('description', '=', $request->input('q'))
                                ->get(); 
            }   
        }

        return $posts;
    }

    protected function users(Request $request) {
        $query = $request->input('q');

        $betweenDoubleQuotes = preg_match('/^".*"$/', $query);
        
        if ($betweenDoubleQuotes) {
            $query = substr($query, 1, -1);
            $users = User::activeUsers()
                        ->where(function($query) use ($request) {
                            $query->Where('username', 'ilike', '%' . substr($request->input('q'), 1, -1) . '%')
                                    ->orWhere('email', 'ilike', '%' . substr($request->input('q'), 1, -1) . '%')
                                    ->orWhere('name', 'ilike', '%' . substr($request->input('q'), 1, -1) . '%');
                        })
                        ->get();              
        }
        else {
            $users = User::activeUsers()
                        ->where(function($query) use ($request) {
                            $query->Where('username', '=', $request->input('q'))
                                    ->orWhere('email', '=', $request->input('q'))
                                    ->orWhereRaw("tsvectors @@ plainto_tsquery('english', ?)", [$request->input('q')])
                                    ->orderByRaw("ts_rank(tsvectors, plainto_tsquery('english', ?)) DESC", [$request->input('q')]);

                        })
                        ->get();
        }

        return $users;
    }

    protected function groups(Request $request) {
        $query = $request->input('q');
        $betweenDoubleQuotes = preg_match('/^".*"$/', $query);

        if ($betweenDoubleQuotes) {
            $query = substr($query, 1, -1);
            $groups = Group::where(function($query) use ($request) {
                            $query->Where('name', 'ilike', '%' . substr($request->input('q'), 1, -1) . '%')
                                    ->orWhere('description', 'ilike', '%' . substr($request->input('q'), 1, -1) . '%');
                        })
                        ->get();
        }
        else {
            $groups = Group::whereRaw("tsvectors @@ plainto_tsquery('english', ?)", [$request->input('q')])
                        ->orderByRaw("ts_rank(tsvectors, plainto_tsquery('english', ?)) DESC", [$request->input('q')])
                        ->orWhere('description', '=', $request->input('q'))
                        ->orWhere('name', '=', $request->input('q'))
                        ->get();
        }

        return $groups;
    }


    protected function comments(Request $request) {
        $query = $request->input('q');
        $betweenDoubleQuotes = preg_match('/^".*"$/', $query);
        if (Auth::guard('webadmin')->check()) {
            if ($betweenDoubleQuotes) {
                $query = substr($query, 1, -1);
                $comments = Comment::Where('content', 'ilike', '%' . substr($request->input('q'), 1, -1) . '%')
                                ->get();
            }
            else {
                $comments = Comment::WhereRaw("tsvectors @@ plainto_tsquery('english', ?)", [$request->input('q')])
                                ->orWhere('content', '=', $request->input('q'))
                                ->get();
            }
        }

        else if (Auth::user()) {
            if ($betweenDoubleQuotes) {
                $query = substr($query, 1, -1);
                $comments = Comment::Where('content', 'ilike', '%' . substr($request->input('q'), 1, -1) . '%')
                                ->whereIn('id', Auth::user()->visibleComments()->pluck('id'))
                                ->get();
            }

            else {
            $comments = Comment::Where(function ($query) use ($request) {
                            $query->WhereRaw("tsvectors @@ plainto_tsquery('english', ?)", [$request->input('q')])
                                  ->orWhere('content', '=', $request->input('q'));
                        })
                        ->whereIn('id', Auth::user()->visibleComments()->pluck('id'))
                        ->get();
            }
        }        
        else {
            if ($betweenDoubleQuotes) {
                $query = substr($query, 1, -1);
                $comments = Comment::publicComments()
                                ->Where('content', 'ilike', '%' . substr($request->input('q'), 1, -1) . '%')
                                ->get();
            }
            else {
                $comments = Comment::publicComments()
                        ->WhereRaw("tsvectors @@ plainto_tsquery('english', ?)", [$request->input('q')])
                        ->orWhere('content', '=', $request->input('q'))
                        ->get();
            }
        }
                    
        return $comments;
    }
}

?>