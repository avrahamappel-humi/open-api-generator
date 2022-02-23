# OpenAPI Generator

WIP.

## Generate OpenAPI documentation

There are two ways to to get the generator to pick up your routes.

The first (and recommended) way is to decorate the controller method with the `Humi\OpenApiGenerator\Attributes\OpenApi` attribute.

```php
<?php

use Humi\OpenApiGenerator\Attributes\OpenApi;

class MyController
{
    #[OpenApi]
    public function index()
    {
        // ...
    }
}
```

The second way is to typehint an instance of the `Humi\OpenApiGenerator\RequestInterface` as the request argument. Any Laravel FormRequest will do, just add the interface to the class.

<!-- prettier-ignore -->
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

-   [ ] Add more OpenAPI data types (enum, max/min, etc.)
-   [ ] Response types
-   [ ] Use $refs
-   [x] Allow opt-in using an `#[Attribute]` instead of typehinting a request object (for instances where the request object isn't used and rules aren't necessary)
