<?php

namespace Tests\Http\Controllers;

use Tests\Http\Requests\StringResponseRequest;

class StringResponseController
{
    public function __invoke(StringResponseRequest $request): string
    {
        return 'Hello world!!';
    }
}
