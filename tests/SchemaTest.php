<?php

namespace Tests;

use Humi\OpenApiGenerator\Schema;
use PHPUnit\Framework\TestCase;

class SchemaTest extends TestCase
{
    /**
     * @test
     */
    public function it_generates_a_schema_from_a_string()
    {
        $schema = Schema::fromType('string');

        self::assertInstanceOf(Schema::class, $schema);
    }

    /**
     * @test
     */
    public function it_does_not_include_a_required_attribute_in_a_string_schema()
    {
        $schema = Schema::fromType('string');

        self::assertArrayNotHasKey('required', $schema->toArray());
    }

    /**
     * @test
     */
    public function it_generates_a_schema_from_an_array()
    {
        $schema = new Schema('array', childSchema: new Schema('string'));

        self::assertInstanceOf(Schema::class, $schema);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_an_array_schema_has_no_child_schema()
    {
        $this->expectExceptionMessage('Schema type was set to `array`, but no child schema was provided.');

        new Schema('array');
    }

    /**
     * @test
     */
    public function it_does_not_include_a_required_attribute_in_an_array_schema()
    {
        $schema = new Schema('array', childSchema: new Schema('string'));

        self::assertArrayNotHasKey('required', $schema->toArray());
    }

    /**
     * @test
     */
    public function it_generates_a_schema_from_an_object()
    {
        $schema = new Schema('object', children: collect(['inner' => new Schema('string')]));

        self::assertInstanceOf(Schema::class, $schema);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_an_object_has_no_children()
    {
        $this->expectExceptionMessage('Schema type was set to `object`, but no children were provided.');

        new Schema('object');
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_the_children_of_an_object_are_not_an_associative_array()
    {
        $this->expectExceptionMessage(
            'An associative array must be provided when creating a Schema of type `object`, received []'
        );

        new Schema('object', children: collect([new Schema('string')]));
    }

    /**
     * @test
     */
    public function it_includes_a_required_attribute_as_an_array_in_an_object_schema()
    {
        $schema = new Schema(
            'object',
            children: collect(['foo' => new Schema('string'), 'bar' => new Schema('string', false)])
        );

        self::assertSame(['foo'], $schema->toArray()['required'] ?? null);
    }

    // it generates a schema from a class with the attribute
}
