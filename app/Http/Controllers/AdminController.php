<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\Administrator;


class AdminController extends Controller
{
    public function show() {
        //$ admin shoulde be the first line of the administrator table
        $username = DB::table('administrators')->first()->username;
        $admin = Administrator::where('username', $username)->firstOrFail();
        return view('pages.admin', ['admin' => $admin]);
    }
}