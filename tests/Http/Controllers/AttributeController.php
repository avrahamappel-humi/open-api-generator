<?php

namespace Tests\Http\Controllers;

use Humi\OpenApiGenerator\Attributes\OpenApi;

class AttributeController
{
    #[OpenApi]
    public function index()
    {
        return 'I love OpenAPI and OpenAPI loves me!!';
    }
}
