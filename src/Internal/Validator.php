<?php
/*
 * Copyright 2021-2026 DATOP (ALTESSA SOLUTIONS) LLC. All rights reserved.
 * Use of this source code is governed by license that can be found in
 * the LICENSE file.
 */

declare(strict_types=1);

namespace Ru\One2Work\Php\DtoValidator\Internal;

use Ru\One2Work\Php\DtoValidator\BaseDto;
use Ru\One2Work\Php\DtoValidator\Contracts\ClassValidatorInterface;
use Ru\One2Work\Php\DtoValidator\Contracts\PropertyValidatorInterface;
use Ru\One2Work\Php\DtoValidator\Exceptions\DtoValidationException;

final class Validator
{
    private array $errors = [];

    public function __construct(private readonly Metadata $metadata) {}

    public function validateProperty(string $property, mixed $value, bool $as_lazy = false): void
    {
        if (! isset($this->metadata->propertyValidator[$property])) {
            return;
        }

        foreach ($this->metadata->propertyValidator[$property] as $validator) {
            if (! ($validator instanceof PropertyValidatorInterface)) {
                continue;
            }
            try {
                $validator->validate($value, $property);
            } catch (DtoValidationException $e) {
                if ($as_lazy) {
                    foreach ($e->getViolations() as $violation) {
                        $this->errors[$property][] = $violation;
                    }
                } else {
                    throw $e;
                }
            }
        }
    }

    public function validateClass(BaseDto $dto, bool $as_lazy = false): void
    {
        if (empty($this->metadata->classValidator)) {
            return;
        }

        foreach ($this->metadata->classValidator as $validator) {
            if (! ($validator instanceof ClassValidatorInterface)) {
                continue;
            }
            try {
                $validator->validate($dto);
            } catch (DtoValidationException $e) {
                if ($as_lazy) {
                    foreach ($e->getViolations() as $violation) {
                        $this->errors[$violation->fieldPath ?? $dto::class][] = $violation;
                    }
                } else {
                    throw $e;
                }
            }
        }
    }

    public function addErrors(string $property, array $errors): void
    {
        $this->errors[$property] = $errors;
    }

    public function addIndexedErrors(string $property, int|string $index, array $errors): void
    {
        $this->errors[$property][$index] = $errors;
    }

    public function resetErrors(): void
    {
        $this->errors = [];
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getPropertyErrors(string $property): array
    {
        return $this->errors[$property] ?? [];
    }

    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }
}
