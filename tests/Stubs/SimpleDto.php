<?php
/*
 * Copyright 2021-2026 DATOP (ALTESSA SOLUTIONS) LLC. All rights reserved.
 * Use of this source code is governed by license that can be found in
 * the LICENSE file.
 */

declare(strict_types=1);

namespace Tests\Stubs;

use Ru\One2Work\Php\DtoValidator\BaseDto;

final class SimpleDto extends BaseDto
{
    public ?string $name = null;

    public ?string $age = null;

    public ?string $address = null;
}
