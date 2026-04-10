<?php
/*
 * Copyright 2021-2026 DATOP (ALTESSA SOLUTIONS) LLC. All rights reserved.
 * Use of this source code is governed by license that can be found in
 * the LICENSE file.
 */

declare(strict_types=1);

namespace Ru\One2Work\Php\DtoValidator\Exceptions;

class DtoValidationException extends \RuntimeException
{
    public function __construct(private readonly array $violations)
    {
        parent::__construct('DTO validation failed');
    }

    public function getViolations(): array
    {
        return $this->violations;
    }
}
