<?php

namespace Tests\Http\Controllers;

use Tests\Http\Requests\LoginRequest;

class LoginController
{
    /**
     * Log into the application.
     *
     * @param  \Tests\Http\Requests\LoginRequest  $request
     */
    public function login(LoginRequest $request)
    {
        return response('Successful login');
    }
}
