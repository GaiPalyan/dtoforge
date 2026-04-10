<?php

declare(strict_types=1);

namespace Ru\One2Work\Php\DtoValidator\Support\Validation;

use Attribute;
use Ru\One2Work\Php\DtoValidator\Contracts\PropertyValidatorInterface;
use Ru\One2Work\Php\DtoValidator\Support\Validation\Traits\HasLaravelValidation;

#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class Required implements PropertyValidatorInterface
{
    use HasLaravelValidation;

    public function __construct() {}

    /**
     * Валидация обязательного значения
     */
    public function validate(mixed $value, string $propertyName): void
    {
        $this->performValidation(
            value: $value,
            rules: ['required'],
            field: $propertyName,
            message: "$propertyName is required"
        );
    }
}
