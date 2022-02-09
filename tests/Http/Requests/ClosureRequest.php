<?php

namespace Tests\Http\Requests;

use Humi\OpenApiGenerator\RequestInterface;

class ClosureRequest implements RequestInterface
{
    public function rules(): array
    {
        return ['foo' => 'bar'];
    }
}
