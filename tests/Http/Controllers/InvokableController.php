<?php

namespace Tests\Http\Controllers;

use Tests\Http\Requests\InvokableRequest;

class InvokableController
{
    public function __invoke(InvokableRequest $request)
    {
        return "Great stuff y'all.";
    }
}
