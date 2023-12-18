<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailModel;


class MailController extends Controller
{
    function send(Request $request) {

        $mailData = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        Mail::to($request->email)->send(new MailModel($mailData));
        return redirect()->route('home');
    }

    public function showContactForm()
    {
        return view('emails.resetForm'); 
    }

}
