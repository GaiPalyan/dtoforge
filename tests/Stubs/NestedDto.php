<?php
/*
 * Copyright 2021-2026 DATOP (ALTESSA SOLUTIONS) LLC. All rights reserved.
 * Use of this source code is governed by license that can be found in
 * the LICENSE file.
 */

declare(strict_types=1);

namespace Tests\Stubs;

use Ru\One2Work\Php\DtoValidator\BaseDto;

/**
 * @method SimpleDto|null getChildren()
 * @method string|null getCompanyAddress()
 * @method string|null getCompanyName()
 * @method static setChildren(?SimpleDto $children)
 * @method static setCompanyAddress(?string $companyAddress)
 * @method static setCompanyName(?string $companyName)
 */
final class NestedDto extends BaseDto
{
    public ?SimpleDto $children = null;

    public ?string $companyAddress = null;

    public ?string $companyName = null;
}
