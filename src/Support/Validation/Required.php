<?php
/*
 * Copyright 2021-2026 DATOP (ALTESSA SOLUTIONS) LLC. All rights reserved.
 * Use of this source code is governed by license that can be found in
 * the LICENSE file.
 */

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
