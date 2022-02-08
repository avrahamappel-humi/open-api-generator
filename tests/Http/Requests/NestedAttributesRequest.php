<?php

namespace Tests\Http\Requests;

use Humi\OpenApiGenerator\RequestInterface;

class NestedAttributesRequest implements RequestInterface
{
    public function rules(): array
    {
        return [
            'data' => 'array|required',
            'data.attributes' => 'array|required',
            'data.attributes.foo' => 'string',
        ];
    }
}
