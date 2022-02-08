<?php

namespace Tests\Http\Controllers;

use Tests\Http\Requests\NestedAttributesRequest;

class NestedAttributesController
{
    public function store(NestedAttributesRequest $request)
    {
        return 'Stored';
    }
}
