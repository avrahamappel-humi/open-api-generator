<?php

namespace Tests;

use Humi\OpenApiGenerator\OpenApiGenerator;
use Illuminate\Support\Facades\Route;
use Tests\Http\Controllers\LoginController;
use Tests\Http\Controllers\NoInterfaceController;

class OpenApiGeneratorTest extends TestCase
{
    /**
     * @test
     */
    public function it_generates_an_openapi_spec_from_a_controller_method_with_the_request_interface()
    {
        Route::post('login', LoginController::class . '@login');

        $yaml = $this->app->call(OpenApiGenerator::class . '@generate');

        self::assertSame(
            file_get_contents(__DIR__ . '/fixtures/login.yml'),
            $yaml
        );
    }

    /**
     * @test
     */
    public function it_doesnt_generate_an_openapi_spec_from_a_controller_method_without_the_request_interface()
    {
        Route::get('stuff', NoInterfaceController::class . '@index');

        self::assertSame(
            '',
            $this->app->call(OpenApiGenerator::class . '@generate')
        );
    }

    // test an invokeable controller
    // test a request with no rules
    // test a request with optional rules
    // test nested attributes
    // test nested attributes with array
}
