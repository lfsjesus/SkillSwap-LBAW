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



}
