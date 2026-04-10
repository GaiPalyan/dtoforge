<?php
/*
 * Copyright 2021-2026 DATOP (ALTESSA SOLUTIONS) LLC. All rights reserved.
 * Use of this source code is governed by license that can be found in
 * the LICENSE file.
 */

declare(strict_types=1);

namespace Ru\One2Work\Php\DtoValidator\Support\Validation;

// Example:
// class ProductDto extends BaseDto
// {
//    #[ArrayOf(PriceDto::class)]
//    public array|null $prices = null;
// }
use Attribute;
use Ru\One2Work\Php\DtoValidator\Contracts\PropertyValidatorInterface;
use Ru\One2Work\Php\DtoValidator\Support\Rules\ArrayOf as ArrayOfRule;
use Ru\One2Work\Php\DtoValidator\Support\Validation\Traits\HasLaravelValidation;

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
