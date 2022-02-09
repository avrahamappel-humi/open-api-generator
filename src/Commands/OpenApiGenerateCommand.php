<?php

namespace Humi\OpenApiGenerator\Commands;

use Humi\OpenApiGenerator\OpenApiGenerator;
use Illuminate\Config\Repository;
use Illuminate\Console\Command;

class OpenApiGenerateCommand extends Command
{
    public $signature = 'open-api:generate';

    public $description = 'Generate OpenAPI documentation for your application.';

    public function handle(OpenApiGenerator $generator, Repository $config)
    {
        $yaml = $generator->generate();

        file_put_contents($config['open-api-generator.file_path'], $yaml);
    }
}
