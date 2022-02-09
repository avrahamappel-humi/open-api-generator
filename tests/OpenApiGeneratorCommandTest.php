<?php

namespace Tests;

use Humi\OpenApiGenerator\OpenApiGenerator;

class OpenApiGeneratorCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        @unlink(base_path('open-api.yml'));
    }

    /**
     * @test
     */
    public function the_command_calls_the_right_method()
    {
        $this->mock(OpenApiGenerator::class)
            ->shouldReceive('generate')
            ->andReturn('YAML DATA');

        $this->artisan('open-api:generate')->execute();

        self::assertSame('YAML DATA', file_get_contents(base_path('open-api.yml')));
    }
}
