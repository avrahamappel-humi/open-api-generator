<?php

namespace Humi\OpenApiGenerator;

use Illuminate\Routing\Route;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

class Action
{
    protected ReflectionMethod $method;
    protected DocBlockFactory $docBlockFactory;
    protected RequestInterface $request;

    public function __construct(Route $route)
    {
        $this->method = $this->reflectControllerMethod($route->getActionName());
        $this->docBlockFactory = DocBlockFactory::createInstance();
        $requests = collect($this->method->getParameters())->filter(
            fn($param) => $this->hasRequestInterfaceParam($param)
        );

        if (count($requests)) {
            $this->request = $this->getRequestObject($requests[0]->getType());
        }
    }

    public function hasRequest(): bool
    {
        return isset($this->request);
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getSummary(): string
    {
        if (!$this->method->getDocComment()) {
            return '';
        }

        return $this->docBlockFactory->create($this->method->getDocComment())->getSummary();
    }

    public function getDescription(): string
    {
        if (!$this->method->getDocComment()) {
            return '';
        }

        return $this->docBlockFactory->create($this->method->getDocComment())->getDescription();
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
}
