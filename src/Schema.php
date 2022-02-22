<?php

namespace Humi\OpenApiGenerator;

use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class Schema implements Arrayable
{
    protected Collection $children;

    protected ?Schema $parent;

    public function __construct(
        protected string $type,
        protected bool $required = true,
        protected string $format = '',
        protected ?Schema $childSchema = null,
        Collection $children = null
    ) {
        if ($type === 'object') {
            throw_if(!$children, new Exception('Schema type was set to `object`, but no children were provided.'));
            throw_if(
                Arr::isList($children->all()),
                new Exception(
                    "An associative array must be provided when creating a Schema of type `object`, received [{$children->implode(
                        ', '
                    )}]"
                )
            );
            $this->children = $children;
            $this->children->each(fn(Schema $child) => ($child->parent = $this));
        }

        if ($type === 'array') {
            throw_if(!$childSchema, new Exception('Schema type was set to `array`, but no child schema was provided.'));
            $this->childSchema->parent = $this;
        }
    }

    public static function fromType(string $type): Schema
    {
        // if class string implements resource interface, get model class, make new resource with new model, call toArray, and figure out type

        return new Schema($type);
    }

    public static function fromValidationRules(array|string $rules): Schema
    {
        if (is_string($rules)) {
            $rules = explode('|', $rules);
        }

        if (Arr::isList($rules)) {
            return static::fromRuleList($rules);
        }

        if (isset($rules['*'])) {
            return new Schema(type: 'array', childSchema: static::fromValidationRules($rules['*']));
        }

        return new Schema(type: 'object', children: static::fromValidationRulesArray(Arr::undot($rules)));
    }

    protected static function fromValidationRulesArray(array $rulesArray): Collection
    {
        return collect($rulesArray)
            ->filter(fn($v, $k) => !is_numeric($k))
            ->map(fn($rules) => static::fromValidationRules($rules));
    }

    protected static function fromRuleList(array $rules): Schema
    {
        $required = true;
        $type = 'string';
        $format = '';

        foreach ($rules as $rule) {
            switch ($rule) {
                // REQUIRED STATUS
                case 'nullable':
                    $required = false;
                    break;

                // DATA TYPE SPECIFICATIONS
                case 'numeric':
                    $type = 'number';
                    break;
                case 'array':
                    $type = 'object';
                    break;

                // FORMAT SPECIFICATIONS
                case 'email':
                    $format = 'email';
                    break;
                case 'password':
                    $format = 'password';
                    break;
            }
        }

        return new Schema(type: $type, required: $required, format: $format);
    }

    public function required(): bool|array
    {
        if ($this->type === 'object') {
            return $this->children->filter
                ->required()
                ->keys()
                ->all();
        }

        return $this->required;
    }

    public function toArray(): array
    {
        $array = [
            'type' => $this->type,
        ];

        if ($required = $this->shouldShowRequired()) {
            $array['required'] = $required;
        }

        if ($this->format) {
            $array['format'] = $this->format;
        }

        if ($this->type === 'object') {
            $array['properties'] = $this->children->toArray();
        }

        if ($this->type === 'array') {
            $array['items'] = $this->childSchema->toArray();
        }

        return $array;
    }

    protected function shouldShowRequired(): bool|array
    {
        if (isset($this->parent) && $this->type !== 'object') {
            return false;
        }

        return $this->required();
    }
}
