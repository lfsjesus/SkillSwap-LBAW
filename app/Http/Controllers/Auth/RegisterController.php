<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;

use Illuminate\View\View;

use App\Models\User;

class RegisterController extends Controller
{
    /**
     * Display a login form.
     */
    public function showRegistrationForm()
    {
        Artisan::call('storage:link');
        if (Auth::check()) {
            return redirect('/home');
        }
        return view('auth.register');
    }

    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|email|max:50|unique:users',
            'username' => 'required|string|max:50|unique:users|not_regex:/^deleted/',
            'password' => 'required|min:8|confirmed',
            'birth_date' => 'required|date|before:18 years ago'
        ],

        $customMessages = [
            'username.not_regex' => 'Username can\'t start with \'deleted\''
        ]);

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'birth_date' => $request->birth_date,
            'password' => bcrypt($request->password)
        ]);

        $credentials = $request->only('email', 'password');
        Auth::attempt($credentials);
        $request->session()->regenerate();
        return redirect()->route('login')
            ->withSuccess('You have successfully registered & logged in!');
    }
}
