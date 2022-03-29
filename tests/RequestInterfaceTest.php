<?php

namespace Tests;

use Humi\OpenApiGenerator\OpenApiGenerator;
use Illuminate\Support\Facades\Route;
use Tests\Http\Controllers\EmptyRulesController;
use Tests\Http\Controllers\LoginController;
use Tests\Http\Controllers\NestedAttributesArrayController;
use Tests\Http\Controllers\NestedAttributesController;
use Tests\Http\Controllers\OptionalRulesController;
use Tests\Http\Requests\ClosureRequest;

class RequestInterfaceTest extends TestCase
{
    /**
     * @test
     */
    public function it_generates_an_openapi_spec_from_a_controller_method_with_the_request_interface()
    {
        Route::post('login', LoginController::class . '@login');

        $yaml = app(OpenApiGenerator::class)->generate();

        self::assertSame(file_get_contents(__DIR__ . '/fixtures/login.yml'), $yaml);
    }

    /**
     * @test
     */
    public function it_doesnt_generate_an_openapi_spec_from_a_closure_route_definition()
    {
        Route::get('closure', function (ClosureRequest $request) {
            return 'Hello world!';
        });

        self::assertSame('', app(OpenApiGenerator::class)->generate());
    }

    /**
     * @test
     */
    public function it_generates_an_open_api_spec_even_if_the_rules_array_is_empty()
    {
        Route::get('empty-rules', EmptyRulesController::class . '@index');

        $yaml = app(OpenApiGenerator::class)->generate();

        self::assertSame(file_get_contents(__DIR__ . '/fixtures/empty-rules.yml'), $yaml);
    }

    /**
     * @test
     */
    public function it_generates_an_openapi_spec_with_optional_rules()
    {
        Route::get('optional-rules', OptionalRulesController::class . '@index');

        $yaml = app(OpenApiGenerator::class)->generate();

        self::assertSame(file_get_contents(__DIR__ . '/fixtures/optional-rules.yml'), $yaml);
    }

    /**
     * @test
     */
    public function it_generates_an_openapi_spec_for_rules_with_nested_attributes()
    {
        Route::post('nested-attributes', NestedAttributesController::class . '@store');

        $yaml = app(OpenApiGenerator::class)->generate();

        self::assertSame(file_get_contents(__DIR__ . '/fixtures/nested-attributes.yml'), $yaml);
    }

    /**
     * @test
     */
    public function it_generates_an_openapi_spec_for_rules_with_nested_attribute_arrays()
    {
        Route::post('nested-attributes', NestedAttributesArrayController::class . '@store');

        $yaml = app(OpenApiGenerator::class)->generate();

        self::assertSame(file_get_contents(__DIR__ . '/fixtures/nested-attributes-array.yml'), $yaml);
    }
}
