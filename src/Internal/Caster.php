<?php
/*
 * Copyright 2021-2026 DATOP (ALTESSA SOLUTIONS) LLC. All rights reserved.
 * Use of this source code is governed by license that can be found in
 * the LICENSE file.
 */

declare(strict_types=1);

namespace Ru\One2Work\Php\DtoValidator\Internal;

use DateTime;
use DateTimeInterface;
use InvalidArgumentException;

final class Caster
{
    public function __construct(private readonly Metadata $metadata) {}

    public function cast(string $property, mixed $value, bool $lazyValidation): mixed
    {
        if ($value === null) {
            return null;
        }

        return $this->castValue(
            $value,
            $this->metadata->propertyTypes[$property],
            $this->metadata->arrayItemTypes[$property] ?? null,
            $lazyValidation,
        );
    }

    private function castValue(mixed $value, string $type, ?string $arrayItemType, bool $lazyValidation): mixed
    {
        return match (true) {
            $type === 'array' && $arrayItemType !== null => $this->castArrayOfObjects($value, $arrayItemType, $lazyValidation),
            $type === 'array' => (array) $value,
            $type === 'string' => (string) $value,
            $type === 'int' => (int) $value,
            $type === 'float' => (float) $value,
            $type === 'bool' => (bool) $value,
            $type === 'DateTime' || $type === DateTimeInterface::class => $this->castDateTime($value),
            class_exists($type) => $this->castObject($value, $type, $lazyValidation),
            default => $value,
        };
    }

    private function castDateTime(mixed $value): DateTime
    {
        if ($value instanceof DateTimeInterface) {
            return DateTime::createFromInterface($value);
        }

        if (is_string($value)) {
            try {
                return new DateTime($value);
            } catch (\Throwable $e) {
                throw new InvalidArgumentException("Cannot parse datetime from string \"{$value}\"", previous: $e);
            }
        }

        throw new InvalidArgumentException('Expected string or DateTimeInterface, got ' . get_debug_type($value));
    }

    private function castObject(mixed $value, string $className, bool $lazyValidation): object
    {
        if (is_object($value) && $value instanceof $className) {
            return $value;
        }

        if (is_array($value) || is_string($value)) {
            return new $className($value, $lazyValidation);
        }

        throw new InvalidArgumentException("Cannot cast value to {$className}");
    }

    /** @return list<object> */
    private function castArrayOfObjects(mixed $value, string $className, bool $lazyValidation): array
    {
        if (! is_array($value)) {
            throw new InvalidArgumentException('Array value expected');
        }

        return array_map(fn ($item) => $this->castObject($item, $className, $lazyValidation), $value);
    }
}
