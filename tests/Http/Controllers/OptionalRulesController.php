<?php

namespace Tests\Http\Controllers;

use Tests\Http\Requests\OptionalRulesRequest;

class OptionalRulesController
{
    public function index(OptionalRulesRequest $request)
    {
        return ['data' => ['Some paginated data'], 'page' => $request->validated()['page']];
    }
}
