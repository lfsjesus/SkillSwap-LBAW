<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailModel;
use App\Models\User;


class MailController extends Controller
{
    public function send(Request $request) {
        $request->validate([
            'email' => 'required|email',
        ]);
    
        $user = User::where('email', $request->email)->first();
    
        if (!$user) {
            // If the user does not exist, redirect back with an error message.
            return back()->withErrors(['email' => "We can't find a user with that email address."]);
        }
    
        $mailData = [
            'name' => $user->name,  // Since we know the user exists, we can directly access the name
            'email' => $request->email,
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
        return view('auth.passwords.reset')->with(['token' => $token]);
    }

    // Reset the password
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        // Reset the password
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = bcrypt($password);
                $user->save();
                // Authenticate the user immediately if desired
            }
        );

        return $status == Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', __($status))
                    : back()->withErrors(['email' => [__($status)]]);
    }


}
