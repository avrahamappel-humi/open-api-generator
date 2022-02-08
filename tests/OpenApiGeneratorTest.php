<?php

namespace Tests;

use Humi\OpenApiGenerator\OpenApiGenerator;
use Illuminate\Support\Facades\Route;
use Tests\Http\Controllers\LoginController;

class OpenApiGeneratorTest extends TestCase
{
    /**
     * @test
     */
    public function it_generates_an_openapi_spec_from_a_controller_method_with_the_request_interface()
    {
        Route::post('login', LoginController::class . '@login');

        $yaml = $this->app->call(OpenApiGenerator::class . '@generate');

        self::assertSame(file_get_contents(__DIR__ . '/fixtures/login.yml'), $yaml);
    }

    // test that controller method without the interface does not get mapped
    // test an invokeable controller
    // test a request with no rules
    // test a request with optional rules
    // test nested attributes
    // test nested attributes with array
}
