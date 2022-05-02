<?php

namespace Tests\Http\Controllers;

use Humi\OpenApiGenerator\Attributes\OpenApi;

class StringResponseController
{
    #[OpenApi]
    public function __invoke(): string
    {
        return 'Hello world!!';
    }
}
