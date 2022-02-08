<?php

namespace Tests\Http\Controllers;

use Tests\Http\Requests\EmptyRulesRequest;

class EmptyRulesController
{
    public function index(EmptyRulesRequest $request)
    {
        return 'YOLO';
    }
}
