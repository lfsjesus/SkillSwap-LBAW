<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailModel;
use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;


class MailController extends Controller
{

    public function send(Request $request) { 
        $request->validate([
            'email' => 'required|email',
        ]);
    
        $user = User::where('email', $request->email)->first();

        $token = Str::random(60);
    
        if (!$user) {
            // If the user does not exist, redirect back with an error message.
            return back()->withErrors(['email' => "We can't find a user with that email address."]);
        }
    
        $mailData = [
            'name' => $user->name,
            'email' => $request->email,
            'token' => $token,
        ];
        
        Mail::to($request->email)->send(new MailModel($mailData));
       
        // Redirect to the 'home' route with a success message.
        return redirect()->route('home')->with('status', 'Password reset link has been sent to your email address.');
    }

    public function showContactForm()
    {
        return view('emails.resetForm'); 
    }

    
    // Show form to reset password (where token is the password reset token)
    public function showResetForm($token)
    {
        return view('emails.choosePassword', compact('token'));
    }

    // Reset the password
    public function reset(Request $request) {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);
    
        // Retrieve the token and email from session
        $sessionToken = $request->session()->get('password_reset_token');
        $sessionEmail = $request->session()->get('password_reset_email');
    
        // Verify the token and email
        if ($request->token === $sessionToken && $request->email === $sessionEmail) {
            // Token and email are valid, so reset the password
            $user = User::where('email', $request->email)->firstOrFail();
            $user->password = bcrypt($request->password);
            $user->save();
    
            // Clear the session variables
            $request->session()->forget('password_reset_token');
            $request->session()->forget('password_reset_email');
    
            return redirect()->route('login')->with('status', 'Your password has been reset.');
        }
    
        // Token or email is invalid, so redirect back with an error
        return back()->withErrors(['token' => 'This password reset token is invalid.']);
    }

}
