<?php

namespace Tests\Http\Controllers;

use Tests\Http\Requests\NestedAttributesArrayRequest;

class NestedAttributesArrayController
{
    public function store(NestedAttributesArrayRequest $request)
    {
        return 'Stored';
    }
}
