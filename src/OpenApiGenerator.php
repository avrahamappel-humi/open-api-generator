<?php

namespace Humi\OpenApiGenerator;

use Humi\OpenApiGenerator\Schema;
use Illuminate\Config\Repository;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Symfony\Component\Yaml\Yaml;

class OpenApiGenerator
{
    public const OPENAPI_VERSION = '3.0.0';

    protected array $config;

    public function __construct(Repository $config, protected Router $router)
    {
        $this->config = $config['open-api-generator'];
    }

    public function generate(): string
    {
        $routes = $this->router->getRoutes()->getRoutes();

        $pathSpecs = $this->generatePathSpecs($routes);

        if (empty($pathSpecs)) {
            return '';
        }

        $spec = [
            'openapi' => self::OPENAPI_VERSION,
            'info' => ['version' => $this->config['version'], 'title' => $this->config['title']],
            'servers' => $this->mapServers(),
            'paths' => $pathSpecs,
        ];

        return Yaml::dump($spec, 20, 4, Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE);
    }

    public function generatePathSpecs(array $routes): array
    {
        return collect($routes)
            ->groupBy(fn(Route $route) => '/' . $route->uri())
            ->map(
                fn($pathGroup) => collect($pathGroup)
                    ->reduce(function ($methodGroup, Route $route) {
                        foreach ($route->methods() as $method) {
                            $methodGroup[strtolower($method)] = $route;
                        }

                        return $methodGroup;
                    }, collect())
                    ->map(fn(Route $route) => $this->generateSpecFromRoute($route))
                    ->filter()
            )
            ->filter->isNotEmpty()
            ->toArray();
    }

    protected function generateSpecFromRoute(Route $route): array
    {
        $action = Action::fromRoute($route);

        if (!$action->canBeMapped()) {
            return [];
        }

        $rules = $action->getRules();

        $spec = [
            'operationId' => $route->getActionMethod(),
            'tags' => $this->generateTags($route),
        ];

        if ($summary = $action->getSummary()) {
            $spec['summary'] = $summary;
        }

        if ($description = $action->getDescription()) {
            $spec['description'] = $description;
        }

        if (!empty($rules)) {
            $spec['requestBody'] = $this->generateRequestBody($rules);
        }

        if ($responses = $this->generateResponses()) {
            $spec['responses'] = $responses;
        }

        return $spec;
    }

    protected function generateRequestBody(array $rules): array
    {
        $schema = Schema::fromValidationRules($rules);

        return [
            'content' => [
                'application/json' => [
                    'schema' => $schema->toArray(),
                ],
            ],
            'required' => !!$schema->required(),
        ];
    }

    protected function generateTags(Route $route): array
    {
        return [class_basename(Arr::first(explode('@', $route->getActionName())))];
    }

    protected function generateResponses(): array
    {
        return ['200' => ['description' => 'Ok']];
    }

    protected function mapServers(): array
    {
        return array_map('array_filter', $this->config['servers']);
    }
}
