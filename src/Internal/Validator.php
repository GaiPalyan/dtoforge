<?php

declare(strict_types=1);

namespace Ru\One2Work\Php\DtoValidator\Internal;

use Ru\One2Work\Php\DtoValidator\BaseDto;
use Ru\One2Work\Php\DtoValidator\Contracts\ClassValidatorInterface;
use Ru\One2Work\Php\DtoValidator\Contracts\PropertyValidatorInterface;
use Ru\One2Work\Php\DtoValidator\Exceptions\DtoValidationException;
use Ru\One2Work\Php\DtoValidator\Support\Violation;

final class Validator
{
    /** @var array<string, mixed> */
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

    /** @param array<string, mixed> $errors */
    public function addErrors(string $property, array $errors): void
    {
        $this->errors[$property] = $errors;
    }

    /** @param array<string, mixed> $errors */
    public function addIndexedErrors(string $property, int|string $index, array $errors): void
    {
        $this->errors[$property][$index] = $errors;
    }

    public function resetErrors(): void
    {
        $this->errors = [];
    }

    /** @return array<string, mixed> */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /** @return list<Violation> */
    public function getPropertyErrors(string $property): array
    {
        return $this->errors[$property] ?? [];
    }

    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }
}
