<?php
/*
 * Copyright 2021-2026 DATOP (ALTESSA SOLUTIONS) LLC. All rights reserved.
 * Use of this source code is governed by license that can be found in
 * the LICENSE file.
 */

declare(strict_types=1);

namespace Tests\Stubs\Validation\Simple;

use Ru\One2Work\Php\DtoValidator\BaseDto;
use Ru\One2Work\Php\DtoValidator\Support\Validation\Required as RequiredValidator;

/**
 * @method self setValue(mixed $value)
 * @method mixed getValue($value)
 */
final class Required extends BaseDto
{
    #[RequiredValidator]
    public mixed $value = null;
}
