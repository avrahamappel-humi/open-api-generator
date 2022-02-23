<?php

namespace Tests;

use Humi\OpenApiGenerator\Attributes\OpenApi;
use Humi\OpenApiGenerator\OpenApiGenerator;
use Illuminate\Support\Facades\Route;
use Tests\Http\Controllers\AttributeController;
use Tests\Http\Controllers\InvokableController;
use Tests\Http\Controllers\NoInterfaceController;

class OpenApiAttributeTest extends TestCase
{
    /**
     * @test
     */
    public function it_generates_an_openapi_spec_from_a_controller_with_the_attribute()
    {
        Route::post('attribute', AttributeController::class . '@index');

        $yaml = app(OpenApiGenerator::class)->generate();

        self::assertSame(file_get_contents(__DIR__ . '/fixtures/attribute.yml'), $yaml);
    }

    /**
     * @test
     */
    public function it_doesnt_generate_an_openapi_spec_from_a_controller_method_without_the_request_interface()
    {
        Route::get('no-interface', NoInterfaceController::class . '@index');

        self::assertSame('', app(OpenApiGenerator::class)->generate());
    }

    /**
     * @test
     */
    public function it_doesnt_generate_an_openapi_spec_from_a_closure_route_definition()
    {
        // prettier-ignore
        Route::get('closure',
            #[OpenApi]
            function () {
                return 'Hello world!';
            }
        );

        self::assertSame('', app(OpenApiGenerator::class)->generate());
    }

    /**
     * @test
     */
    public function it_generates_an_openapi_spec_from_an_invokable_controller()
    {
        Route::post('invokable', InvokableController::class);

        $yaml = app(OpenApiGenerator::class)->generate();

        self::assertSame(file_get_contents(__DIR__ . '/fixtures/invokable.yml'), $yaml);
    }
}
