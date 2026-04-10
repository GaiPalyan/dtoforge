<?php

declare(strict_types=1);

namespace Forge\Dto\Contracts;

use Forge\Dto\Exceptions\DtoValidationException;

interface PropertyValidatorInterface
{
    /**
     * @throws DtoValidationException
     */
    public function validate(mixed $value, string $propertyName): void;
}
