<?php

namespace Humi\OpenApiGenerator\Objects;

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
        protected string $childType = '',
        Collection $children = null
    ) {
        if ($type === 'object') {
            throw_if(!$children, new Exception('Schema type was set to `object`, but no children were provided.'));
            throw_if(
                array_is_list($children->all()),
                new Exception(
                    "An associative array must be provided when creating a Schema of type `object`, received [{$children->implode(
                        ', '
                    )}]"
                )
            );
            $this->children = $children ?? new Collection();
            $this->children->each(fn(Schema $child) => $child->setParent($this));
        }

        if ($type === 'array') {
            throw_if(!$childType, new Exception('Schema type was set to `array`, but no child type was provided.'));
        }
    }

    public static function fromValidationRules(array $rules): static
    {
        return new static(type: 'object', children: static::fromRules(Arr::undot($rules)));
    }

    protected static function fromRules(array $rules): Collection
    {
        return collect($rules)->map(function (array|string $rule, string $field) {
            $rules = is_string($rule) ? explode('|', $rule) : $rule;

            if (!array_is_list($rules)) {
                return new Schema(type: 'object', children: static::fromRules($rules));
            }

            return static::fromRuleList($field, $rules);
        });
    }

    protected static function fromRuleList(string $field, array $rules): static
    {
        $required = true;
        $type = 'string';
        $min = '';
        $max = '';
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
                // TODO figure out what to do with arrays
                /* case '*': */
                /*     $type = 'array'; */
                /*     break; */

                // FORMAT SPECIFICATIONS
                case 'email':
                    $format = 'email';
                    break;
                case 'password':
                    $format = 'password';
                    break;
            }
        }

        $fieldSchema = [
            'type' => $type,
        ];

        if ($min) {
            $fieldSchema[$type === 'string' ? 'minLength' : 'minimum'] = $min;
        }

        if ($max) {
            $fieldSchema[$type === 'string' ? 'maxLength' : 'maximum'] = $max;
        }

        if ($format) {
            $fieldSchema['format'] = $format;
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

        if (!isset($this->parent)) {
            $array['required'] = $this->required();
        }

        if ($this->format) {
            $array['format'] = $this->format;
        }

        if ($this->type === 'object') {
            $array['properties'] = $this->children->toArray();
        }

        if ($this->type === 'array') {
            $array['items'] = ['type' => $this->childType];
        }

        return $array;
    }

    protected function setParent(Schema $parent)
    {
        $this->parent = $parent;
    }
}
