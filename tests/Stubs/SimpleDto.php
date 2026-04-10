<?php

declare(strict_types=1);

namespace Tests\Stubs;

use Forge\Dto\BaseDto;

/**
 * @method string|null getName()
 * @method string|null getAge()
 * @method string|null getAddress()
 * @method static setName(?string $name)
 * @method static setAge(mixed $age)
 * @method static setAddress(?string $address)
 */
final class SimpleDto extends BaseDto
{
    public ?string $name = null;

    public ?string $age = null;

    public ?string $address = null;
}
