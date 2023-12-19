<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use ValidatesRequests;
    use AuthorizesRequests {
        authorize as protected baseAuthorize;
    }
    public function authorize($ability, $arguments = [])
    {
        if (Auth::guard('webadmin')->check()) {
            Auth::shouldUse('webadmin');
        }

        $this->baseAuthorize($ability, $arguments);
    }

}
