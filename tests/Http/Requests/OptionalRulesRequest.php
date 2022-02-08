<?php

namespace Tests\Http\Requests;

use Humi\OpenApiGenerator\RequestInterface;
use Illuminate\Foundation\Http\FormRequest;

class OptionalRulesRequest extends FormRequest implements RequestInterface
{
    public function rules(): array
    {
        return [
            'page' => 'nullable|numeric|min:1',
        ];
    }
}
