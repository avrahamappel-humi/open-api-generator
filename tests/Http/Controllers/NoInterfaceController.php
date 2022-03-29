<?php

namespace Tests\Http\Controllers;

class NoInterfaceController
{
    public function index()
    {
        return response("Here's some stuff.");
    }
}
