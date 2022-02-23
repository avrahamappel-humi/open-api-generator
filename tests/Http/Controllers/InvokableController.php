<?php

namespace Tests\Http\Controllers;

use Humi\OpenApiGenerator\Attributes\OpenApi;

class InvokableController
{
    #[OpenApi]
    public function __invoke()
    {
        return "Great stuff y'all.";
    }
}
