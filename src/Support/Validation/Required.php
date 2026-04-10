<?php

declare(strict_types=1);

namespace Forge\Dto\Support\Validation;

use Attribute;
use Forge\Dto\Contracts\PropertyValidatorInterface;
use Forge\Dto\Support\Validation\Traits\HasLaravelValidation;

#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class Required implements PropertyValidatorInterface
{
    use HasLaravelValidation;

    public function __construct() {}

    /**
     * Validates that the value is not empty
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
