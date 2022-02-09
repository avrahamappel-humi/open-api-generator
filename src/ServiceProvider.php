<?php

namespace Humi\OpenApiGenerator;

use Humi\OpenApiGenerator\Commands\OpenApiGenerateCommand;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/open-api-generator.php', 'open-api-generator');

        if ($this->app->runningInConsole()) {
            $this->commands([OpenApiGenerateCommand::class]);
        }

        $this->publishes([__DIR__ . '/../config/open-api-generator.php', config_path('open-api-generator.php')]);
    }
}
