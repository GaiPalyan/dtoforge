<?php

declare(strict_types=1);

namespace Ru\One2Work\Php\DtoValidator\Contracts;

use Ru\One2Work\Php\DtoValidator\Exceptions\DtoValidationException;

interface PropertyValidatorInterface
{
    /**
     * @throws DtoValidationException
     */
    public function validate(mixed $value, string $propertyName): void;
}
