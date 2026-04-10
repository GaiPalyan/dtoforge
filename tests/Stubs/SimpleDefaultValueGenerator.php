<?php
/*
 * Copyright 2021-2026 DATOP (ALTESSA SOLUTIONS) LLC. All rights reserved.
 * Use of this source code is governed by license that can be found in
 * the LICENSE file.
 */

declare(strict_types=1);

namespace Tests\Stubs;

use Attribute;
use Ru\One2Work\Php\DtoValidator\BaseDto;
use Ru\One2Work\Php\DtoValidator\Contracts\DefaultValueGeneratorInterface;

#[Attribute(Attribute::TARGET_PROPERTY)]
class SimpleDefaultValueGenerator implements DefaultValueGeneratorInterface
{
    public function __construct() {}

    public function generate(BaseDto $dto): ?string
    {
        return 'Generated value';
    }

    public function supports(BaseDto $dto, string $propertyName): bool
    {
        $value = $dto->{$propertyName} ?? null;

        return empty($dto->getValidationErrors()) && ($value === null || $value === '');
    }
}
