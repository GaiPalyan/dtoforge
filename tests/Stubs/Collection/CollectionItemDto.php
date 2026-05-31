<?php

declare(strict_types=1);

namespace Tests\Stubs\Collection;

use Forge\Dto\BaseDto;

/**
 * @method string|null getLabel()
 * @method static setLabel(?string $label)
 */
final class CollectionItemDto extends BaseDto
{
    public ?string $label = null;
}
