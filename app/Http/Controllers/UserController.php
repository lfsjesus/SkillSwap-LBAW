<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\User;

class UserController extends Controller
{
    public function show() {

        if (Auth::check()) {
            $user = User::find(Auth::user()->id);
            return view('pages.user', ['user' => $this->user]);
        }
        
    }
}