<?php
/*
 * Copyright 2021-2026 DATOP (ALTESSA SOLUTIONS) LLC. All rights reserved.
 * Use of this source code is governed by license that can be found in
 * the LICENSE file.
 */

declare(strict_types=1);

namespace Ru\One2Work\Php\DtoValidator;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use InvalidArgumentException;
use Ru\One2Work\Php\DtoValidator\Exceptions\DtoValidationException;
use Ru\One2Work\Php\DtoValidator\Exceptions\UnknownPropertyException;
use Ru\One2Work\Php\DtoValidator\Internal\Caster;
use Ru\One2Work\Php\DtoValidator\Internal\Metadata;
use Ru\One2Work\Php\DtoValidator\Internal\Serializer;
use Ru\One2Work\Php\DtoValidator\Internal\Validator;

/**
 * Base Data Transfer Object class with automatic type casting and validation.
 *
 * Features:
 * - Automatic type casting based on property types
 * - Support for nested DTOs
 * - Array of objects support via PHPDoc annotations
 * - DateTime handling
 * - JSON serialization/deserialization
 * - Immutable operations (clone, merge)
 * - Comparison utilities
 *
 * @template T of BaseDto
 * ## Usage Examples:
 *
 * ### Creating a DTO:
 * ```php
 * // From array
 * $dto = new UserDto([
 * 'name' => 'John',
 * 'createdAt' => '2023-01-01',
 * 'addresses' => [['city' => 'New York']],
 * ]);
 *
 * // From JSON
 * $dto = new UserDto($jsonString);
 * ```
 *
 * ### Accessing Data:
 * ```php
 * $name = $dto->getName();
 * $dto->setCreatedAt(new DateTime());
 * $hasDate = $dto->hasCreatedAt();
 * ```
 *
 * ### Modifying:
 * ```php
 * $newDto = $dto->clone()->setName('Jane');
 * ```
 *
 * ### Merging DTOs:
 * ```php
 * $dto1 = new UserDto(['name' => 'John', 'age' => 25]);
 * $dto2 = new UserDto(['name' => 'Jane', 'email' => 'jane@example.com']);
 * $merged = $dto1->merge($dto2);
 * // Result: ['name' => 'Jane', 'age' => 25, 'email' => 'jane@example.com']
 * ```
 *
 * ### Comparing DTOs:
 * ```php
 * $dto1 = new UserDto(['name' => 'John', 'age' => 25]);
 * $dto2 = new UserDto(['name' => 'John', 'age' => 30]);
 * $diff = $dto1->diff($dto2);
 * // Result: ['age' => ['old' => 25, 'new' => 30]]
 * ```
 *
 * ### Serialization:
 * ```php
 * $dto = new UserDto([
 * 'name' => 'John',
 * 'createdAt' => new DateTime('2023-01-01'),
 * 'addresses' => [
 * new AddressDto(['city' => 'New York']),
 * new AddressDto(['city' => 'London']),
 * ],
 * ]);
 *
 * $array = $dto->toArray();
 * $json = $dto->toJson(JSON_PRETTY_PRINT);
 * ```
 */
abstract class BaseDto implements Arrayable, Jsonable
{
    private Metadata $metadata;

    private Caster $caster;

    private Validator $validator;

    private Serializer $serializer;

    /**
     * @param  bool  $lazyValidation  by default validation running on setting value
     */
    public function __construct(
        array|string|null $data = [],
        private readonly bool $lazyValidation = false
    ) {
        $this->metadata = Metadata::for(static::class);
        $this->caster = new Caster($this->metadata);
        $this->validator = new Validator($this->metadata);
        $this->serializer = new Serializer($this->metadata);

        $this->fill($data);
    }

    public function __call(string $method, array $arguments): mixed
    {
        $prefix = substr($method, 0, 3);
        $property = lcfirst(substr($method, 3));

        return match ($prefix) {
            'get' => $this->get($property),
            'set' => $this->set($property, $arguments[0] ?? null),
            'has' => $this->has($property),
            default => throw new \BadMethodCallException("Method {$method} does not exist"),
        };
    }

    public function toArray(bool $clearing = false, bool $masking = false): array
    {
        return $this->serializer->toArray($this, $clearing, $masking);
    }

    public function toJson($options = JSON_UNESCAPED_UNICODE, bool $clearing = true, bool $masking = false): ?string
    {
        return $this->serializer->toJson($this, $options, $clearing, $masking);
    }

    /**
     * Creates a new instance of the DTO with the same data
     *
     * @return T
     */
    public function clone(): static
    {
        return new static($this->toArray());
    }

    /**
     * Merges current DTO with another one
     *
     * @param  T  $dto  DTO to merge with
     * @return T New DTO instance with merged data
     */
    public function merge(self $dto): static
    {
        $new = $this->clone();
        foreach ($dto->getProperties() as $property => $value) {
            if ($value !== null) {
                $new->set($property, $value);
            }
        }

        if (! $this->lazyValidation) {
            $new->validator->validateClass($new);
        }

        return $new;
    }

    /**
     * Compares current DTO with another one and returns differences
     *
     * @param  T  $dto  DTO to compare with
     * @return array<string, array{old: mixed, new: mixed}>
     */
    public function diff(self $dto): array
    {
        $diff = [];
        foreach ($this->getProperties() as $property => $value) {
            $otherValue = $dto->get($property);
            if ($value !== $otherValue) {
                $diff[$property] = [
                    'old' => $value,
                    'new' => $otherValue,
                ];
            }
        }
        return $diff;
    }

    /**
     * Validates all properties of the DTO
     */
    public function passes(): bool
    {
        $this->validator->resetErrors();

        try {
            $this->validator->validateClass($this, true);
            foreach ($this->metadata->propertyTypes as $propertyName => $typeName) {
                $value = $this->{$propertyName};

                if ($this->isDtoType($typeName)) {
                    $this->validateDtoProperty($propertyName, $value);
                } elseif (is_array($value)) {
                    $this->validateArrayProperty($propertyName, $value);
                } else {
                    $this->validator->validateProperty($propertyName, $value, true);
                }
            }
        } catch (InvalidArgumentException|DtoValidationException) {
            return false;
        }

        return ! $this->validator->hasErrors();
    }

    /**
     * Returns validation errors
     */
    public function getValidationErrors(): array
    {
        return $this->validator->getErrors();
    }

    /**
     * Returns validation errors for a specific property
     *
     * @param  string  $propertyName  Property name (supports both camelCase and snake_case)
     * @return array Validation errors for the property, empty array if property has no errors
     * @throws InvalidArgumentException If property does not exist
     */
    public function getPropertyValidationErrors(string $propertyName): array
    {
        return $this->validator->getPropertyErrors(
            $this->resolvePropertyName($propertyName)
        );
    }

    /**
     * Determine if there are errors for a specific property
     *
     * @param  string  $propertyName  Property name (supports both camelCase and snake_case)
     * @throws InvalidArgumentException If property does not exist
     */
    public function hasPropertyValidationErrors(string $propertyName): bool
    {
        return ! empty($this->getPropertyValidationErrors($propertyName));
    }

    /**
     * Generate defaults if supporting & politics allowed
     */
    public function generateDefaultsIfAllowed(): void
    {
        foreach ($this->metadata->defaultValueGenerators as $property => $generator) {
            if ($generator->supports($this, $property)) {
                $this->set($property, $generator->generate($this));
            }
        }
    }

    public function fill(array|string|null $data): self
    {
        if (! empty($data)) {
            if (is_string($data)) {
                $data = json_decode($data, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new InvalidArgumentException('Invalid JSON string provided: ' . json_last_error_msg());
                }
            }

            if (is_array($data)) {
                foreach ($data as $key => $value) {
                    try {
                        $this->set($key, $value);
                    } catch (UnknownPropertyException) {
                        // NOTE: field name don't exist
                    }
                }
            }

            if (! $this->lazyValidation) {
                $this->validator->validateClass($this);
            }
        }

        return $this;
    }

    protected function set(string $property, mixed $value): self
    {
        $property = $this->resolvePropertyName($property);

        if (! $this->lazyValidation) {
            $this->validator->validateProperty($property, $value);
        }

        $this->{$property} = $this->caster->cast($property, $value, $this->lazyValidation);

        return $this;
    }

    protected function get(string $property): mixed
    {
        return $this->{$this->resolvePropertyName($property)};
    }

    protected function has(string $property): bool
    {
        try {
            $property = $this->resolvePropertyName($property);
        } catch (InvalidArgumentException) {
            return false;
        }

        return isset($this->{$property});
    }

    private function validateDtoProperty(string $propertyName, mixed $value): void
    {
        if (! is_null($value)) {
            if (! $value->passes()) {
                $this->validator->addErrors($propertyName, $value->getValidationErrors());
            }
        } else {
            $this->validator->validateProperty($propertyName, null, true);
        }
    }

    private function validateArrayProperty(string $propertyName, array $items): void
    {
        foreach ($items as $index => $item) {
            if ($item instanceof self && ! $item->passes()) {
                $this->validator->addIndexedErrors($propertyName, $index, $item->getValidationErrors());
            }
        }

        $this->validator->validateProperty($propertyName, $items, true);
    }

    private function isDtoType(string $typeName): bool
    {
        return is_subclass_of($typeName, self::class);
    }

    private function getProperties(): array
    {
        return array_intersect_key(get_object_vars($this), $this->metadata->propertyTypes);
    }

    /**
     * Resolves property name trying different naming styles
     *
     * @param  string  $property  Original property name
     * @return string Resolved property name
     * @throws UnknownPropertyException If property does not exist
     */
    private function resolvePropertyName(string $property): string
    {
        if (property_exists($this, $property)) {
            return $property;
        }

        $camelCaseProperty = $this->snakeToCamel($property);
        if ($camelCaseProperty !== $property && property_exists($this, $camelCaseProperty)) {
            return $camelCaseProperty;
        }

        $snakeCaseProperty = $this->camelToSnake($property);
        if ($snakeCaseProperty !== $property && property_exists($this, $snakeCaseProperty)) {
            return $snakeCaseProperty;
        }

        throw new UnknownPropertyException("Property {$property} does not exist");
    }

    private function snakeToCamel(string $value): string
    {
        return lcfirst(str_replace('_', '', ucwords($value, '_')));
    }

    private function camelToSnake(string $value): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $value));
    }
}
