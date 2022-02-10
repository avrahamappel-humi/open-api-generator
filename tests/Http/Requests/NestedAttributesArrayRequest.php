<?php

namespace Tests\Http\Requests;

use Humi\OpenApiGenerator\RequestInterface;

class NestedAttributesArrayRequest implements RequestInterface
{
    public function rules(): array
    {
        return [
            'data' => ['array', 'required'],
            'data.*.attributes' => ['array', 'required'],
            'data.*.attributes.foo' => 'string',
        ];
    }
}
