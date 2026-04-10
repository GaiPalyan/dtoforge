<?php
/*
 * Copyright 2021-2026 DATOP (ALTESSA SOLUTIONS) LLC. All rights reserved.
 * Use of this source code is governed by license that can be found in
 * the LICENSE file.
 */

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
