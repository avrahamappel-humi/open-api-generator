<?php

namespace Humi\OpenApiGenerator;

use Humi\OpenApiGenerator\Objects\Schema;
use Illuminate\Config\Repository;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use Symfony\Component\Yaml\Yaml;

class OpenApiGenerator
{
    public const OPENAPI_VERSION = '3.0.0';

    public array $config;

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

        dump($spec);

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
            ->filter()
            ->toArray();
    }

    protected function generateSpecFromRoute(Route $route): array
    {
        $reflectionMethod = new ReflectionMethod(str_replace('@', '::', $route->getActionName()));

        $requests = collect($reflectionMethod->getParameters())->filter(
            fn($param) => $this->hasRequestInterfaceParam($param)
        );

        if ($requests->isEmpty()) {
            return [];
        }

        $request = $requests[0];
        $requestObject = $this->getRequestObject($request->getType());
        $hasRules = !empty($requestObject->rules());

        $spec = [
            'summary' => $this->generateSummary($reflectionMethod),
            'operationId' => $route->getActionMethod(),
            'tags' => $this->generateTags($route),
            'responses' => $this->generateResponses($hasRules),
        ];

        if ($hasRules) {
            $spec['requestBody'] = $this->generateRequestBody($requestObject);
        }

        return $spec;
    }

    protected function hasRequestInterfaceParam(ReflectionParameter $param)
    {
        $type = $param->getType();

        if (!($type instanceof ReflectionNamedType)) {
            return false;
        }

        return class_exists($type->getName()) &&
            array_search(RequestInterface::class, class_implements($type->getName()));
    }

    protected function getRequestObject(ReflectionNamedType $type): RequestInterface
    {
        $class = $type->getName();

        return new $class();
    }

    protected function generateSummary(ReflectionMethod $method): string
    {
        dump($method);
        return $method->getDocComment();
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

    protected function generateResponses(bool $hasValidation): array
    {
        return array_replace(
            [
                '200' => [
                    'description' => 'Success',
                ],
            ],
            $hasValidation
                ? [
                    '422' => [
                        'description' => 'Invalid submission',
                        'content' => [
                            'application/json' => [
                                'schema' => (new Schema(
                                    type: 'object',
                                    children: collect([
                                        'message' => new Schema(type: 'string'),
                                        'errors' => new Schema(type: 'array', childType: 'string'),
                                    ])
                                ))->toArray(),
                            ],
                        ],
                    ],
                ]
                : []
        );
    }

    protected function mapServers(): array
    {
        return array_map('array_filter', $this->config['servers']);
    }
}
