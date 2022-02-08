<?php

namespace Tests\Http\Requests;

use Humi\OpenApiGenerator\RequestInterface;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest implements RequestInterface
{
    public function rules(): array
    {
        return [
            'email' => 'email',
            'password' => 'password',
        ];
    }
}
