<?php

namespace Tests\Http\Requests;

use Humi\OpenApiGenerator\RequestInterface;

class StringResponseRequest implements RequestInterface
{
    public function rules(): array
    {
        return [];
    }
}
