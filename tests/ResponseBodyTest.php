<?php

namespace Tests;

use Illuminate\Support\Facades\Route;
use Tests\Http\Controllers\StringResponseController;

class ResponseBodyTest extends TestCase
{
    /**
     * @test
     */
    public function it_generates_a_response_schema_from_a_string()
    {
        Route::get('string', StringResponseController::class);

        $yaml = app(OpenApiGenerator::class)->generate();

        self::assertSame(file_get_contents(__DIR__ . '/fixtures/string-response.yml'), $yaml);
    }
    // it generates a response schema from an array
    // it generates a response schema from a nested array
    // it generates a response schema from an instance of the resource interface
}
