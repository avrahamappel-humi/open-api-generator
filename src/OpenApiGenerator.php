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
        $action = new Action($route);

        if (!$action->hasRequest()) {
            return [];
        }

        $request = $action->getRequest();
        $hasRules = !empty($request->rules());

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

        if ($hasRules) {
            $spec['requestBody'] = $this->generateRequestBody($request);
        }

        if ($responses = $this->generateResponses()) {
            $spec['responses'] = $responses;
        }

        return $spec;
    }

    protected function generateRequestBody(RequestInterface $request): array
    {
        $schema = Schema::fromValidationRules($request->rules());

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
        return [];
    }

    protected function mapServers(): array
    {
        return array_map('array_filter', $this->config['servers']);
    }
}
