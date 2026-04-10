<?php

declare(strict_types=1);

namespace Forge\Dto\Support\Validation;

// Example:
// class ProductDto extends BaseDto
// {
//    #[ArrayOf(PriceDto::class)]
//    public array|null $prices = null;
// }
use Attribute;
use Forge\Dto\Contracts\PropertyValidatorInterface;
use Forge\Dto\Support\Rules\ArrayOf as ArrayOfRule;
use Forge\Dto\Support\Validation\Traits\HasLaravelValidation;

#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class ArrayOf implements PropertyValidatorInterface
{
    use HasLaravelValidation;

    public function __construct(
        public string $type,
    ) {
        if (! class_exists($type) && ! interface_exists($type)) {
            throw new \InvalidArgumentException("Type {$type} does not exist. Make sure the class/interface is loaded.");
        }
    }

    public function validate(mixed $value, string $propertyName): void
    {
        $this->performValidation(
            value: $value,
            rules: ['nullable', new ArrayOfRule($this->type)],
            field: $propertyName,
        );
    }
}
