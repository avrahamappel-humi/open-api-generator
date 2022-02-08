<?php

namespace Tests\Http\Controllers;

use Illuminate\Http\Request;

class NoInterfaceController
{
    public function index(Request $request)
    {
        return response("Here's some stuff.");
    }
}
