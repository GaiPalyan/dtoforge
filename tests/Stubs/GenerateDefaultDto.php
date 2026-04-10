<?php

declare(strict_types=1);

namespace Tests\Stubs;

use Forge\Dto\BaseDto;

/**
 * @method string|null getValue()
 */
final class GenerateDefaultDto extends BaseDto
{
    #[SimpleDefaultValueGenerator]
    public ?string $value = null;
}
