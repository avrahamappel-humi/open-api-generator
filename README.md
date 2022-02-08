# OpenAPI Generator

Very basic.

## Generate OpenAPI documentation

For the generator to pick up your routes, you need to typehint an instance of the `Humi\OpenApiGenerator\RequestInterface` as your request. Any Laravel FormRequest will do, just add the interface to the class.

```php
<?php

use Illuminate\Http\FormRequest;
use Humi\OpenApiGenerator\RequestInterface;

class MyRequest extends FormRequest implements RequestInterface
{
    public function rules(): array
    {
        return [
            // Some validation rules ...
        ];
    }
}
```

In your controller:

```php
<?php

class MyController
{
    public function store(MyRequest $request)
    {
        // ...
    }
}
```

Run the following command to generate documentation:

```sh
php artisan open-api:generate
```

The generated OpenAPI spec will be located in `open-api.yml` at the root of your project (the file name can be configured in `open-api-generator.php`).

## What's left to do?

-   [ ] Finish tests
-   [ ] Add publishing to service provider
-   [ ] Add OpenAPI verification to the test files
-   [ ] Add more OpenAPI data types (enum, max/min, etc.)
