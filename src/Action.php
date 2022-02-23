<?php

namespace Humi\OpenApiGenerator;

use Humi\OpenApiGenerator\Attributes\OpenApi;
use Illuminate\Routing\Route;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

class Action
{
    protected ReflectionMethod $reflection;

    public function __construct(Route $route, protected DocBlockFactory $docBlockFactory)
    {
        $this->setReflection($route);
    }

    public static function fromRoute(Route $route): Action
    {
        return app(Action::class, ['route' => $route]);
    }

    public function canBeMapped(): bool
    {
        return $this->hasAttribute() || $this->hasRequestInterface();
    }

    public function getRules(): array
    {
        if (!isset($this->reflection)) {
            return [];
        }

        $requests = collect($this->reflection->getParameters())->filter(
            fn($param) => $this->hasRequestInterfaceParam($param)
        );

        if ($requests->isEmpty()) {
            return [];
        }

        $request = $this->getRequestObject($requests[0]->getType());

        return $request->rules();
    }

    public function getSummary(): string
    {
        if (!$this->reflection->getDocComment()) {
            return '';
        }

        return $this->docBlockFactory->create($this->reflection->getDocComment())->getSummary();
    }

    public function getDescription(): string
    {
        if (!$this->reflection->getDocComment()) {
            return '';
        }

        return $this->docBlockFactory->create($this->reflection->getDocComment())->getDescription();
    }

    protected function setReflection(Route $route): void
    {
        if ($route->getActionName() === 'Closure') {
            return;
        }

        $this->reflection = $this->reflectControllerMethod($route->getActionName());
    }

    protected function reflectControllerMethod(string $actionName): ReflectionMethod
    {
        if (str_contains($actionName, '@')) {
            return new ReflectionMethod(...explode('@', $actionName));
        }

        if (method_exists($actionName, '__invoke')) {
            return new ReflectionMethod($actionName, '__invoke');
        }

        return new ReflectionMethod($actionName);
    }

    protected function hasAttribute(): bool
    {
        if (!isset($this->reflection)) {
            return false;
        }

        return count($this->reflection->getAttributes(OpenApi::class));
    }

    protected function hasRequestInterface(): bool
    {
        if (!isset($this->reflection)) {
            return false;
        }

        return collect($this->reflection->getParameters())
            ->filter(fn($param) => $this->hasRequestInterfaceParam($param))
            ->isNotEmpty();
    }

    protected function hasRequestInterfaceParam(ReflectionParameter $param): bool
    {
        $type = $param->getType();

        if (!($type instanceof ReflectionNamedType)) {
            return false;
        }

        if (!class_exists($type->getName())) {
            return false;
        }

        return array_search(RequestInterface::class, class_implements($type->getName())) ||
            method_exists($type->getName(), 'rules');
    }

    protected function getRequestObject(ReflectionNamedType $type): RequestInterface
    {
        $class = $type->getName();

        return new $class();
    }
}
