<?php

declare(strict_types=1);

namespace Forge\Dto;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use InvalidArgumentException;
use Forge\Dto\Exceptions\DtoValidationException;
use Forge\Dto\Exceptions\UnknownPropertyException;
use Forge\Dto\Internal\Caster;
use Forge\Dto\Internal\Metadata;
use Forge\Dto\Internal\Serializer;
use Forge\Dto\Internal\Validator;
use Forge\Dto\Support\Violation;

/**
 * Base Data Transfer Object with automatic type casting and validation.
 *
 * @phpstan-consistent-constructor
 * @implements Arrayable<string, mixed>
 */
abstract class BaseDto implements Arrayable, Jsonable
{
    private Metadata $metadata;

    private Caster $caster;

    private Validator $validator;

    private Serializer $serializer;

    /**
     * @param  array<string, mixed>|string|null  $data
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

    /** @param array<int, mixed> $arguments */
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

    /** @return array<string, mixed> */
    public function toArray(bool $clearing = false, bool $masking = false): array
    {
        return $this->serializer->toArray($this, $clearing, $masking);
    }

    public function toJson($options = JSON_UNESCAPED_UNICODE, bool $clearing = true, bool $masking = false): ?string
    {
        return $this->serializer->toJson($this, $options, $clearing, $masking);
    }

    public function clone(): static
    {
        return new static($this->toArray());
    }

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

    /** @return array<string, array{old: mixed, new: mixed}> */
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

    /** @return array<string, mixed> */
    public function getValidationErrors(): array
    {
        return $this->validator->getErrors();
    }

    /**
     * @return list<Violation>
     * @throws InvalidArgumentException
     */
    public function getPropertyValidationErrors(string $propertyName): array
    {
        return $this->validator->getPropertyErrors(
            $this->resolvePropertyName($propertyName)
        );
    }

    /** @throws InvalidArgumentException */
    public function hasPropertyValidationErrors(string $propertyName): bool
    {
        return ! empty($this->getPropertyValidationErrors($propertyName));
    }

    public function generateDefaultsIfAllowed(): void
    {
        foreach ($this->metadata->defaultValueGenerators as $property => $generator) {
            if ($generator->supports($this, $property)) {
                $this->set($property, $generator->generate($this));
            }
        }
    }

    /** @param array<string, mixed>|string|null $data */
    public function fill(array|string|null $data): static
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

    protected function set(string $property, mixed $value): static
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

    /** @param array<int|string, mixed> $items */
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

    /** @return array<string, mixed> */
    private function getProperties(): array
    {
        return array_intersect_key(get_object_vars($this), $this->metadata->propertyTypes);
    }

    /** @throws UnknownPropertyException */
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
