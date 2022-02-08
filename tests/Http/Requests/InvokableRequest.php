<?php

namespace Tests\Http\Requests;

use Humi\OpenApiGenerator\RequestInterface;

class InvokableRequest implements RequestInterface
{
    public function rules(): array
    {
        return [
            'foo' => 'required|string|max:3',
            'sin' => 'required|numeric|min:9',
        ];
    }
}
