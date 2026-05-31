<?php

declare(strict_types=1);

namespace Tests\Stubs\Collection;

use Forge\Dto\BaseDto;
use Forge\Dto\Support\Casting\CastEachTo;

/**
 * @method CollectionItemDto[]|null getItems()
 * @method static setItems(?array $items)
 */
final class CollectionDto extends BaseDto
{
    /** @var CollectionItemDto[]|null */
    #[CastEachTo(CollectionItemDto::class)]
    public ?array $items = null;
}
