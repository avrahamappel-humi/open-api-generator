<?php

namespace Tests\Http\Requests;

use Humi\OpenApiGenerator\RequestInterface;

class EmptyRulesRequest implements RequestInterface
{
    public function rules(): array
    {
        return [];
    }
}
