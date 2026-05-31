<?php

declare(strict_types=1);

namespace Forge\Dto\Support\Casting;

// Example:
// class ProductDto extends BaseDto
// {
//    #[CastEachTo(PriceDto::class)]
//    public array|null $prices = null;
// }
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class CastEachTo
{
    /**
     * @param  class-string  $type
     */
    public function __construct(
        public string $type,
    ) {
        if (! class_exists($type)) {
            throw new \InvalidArgumentException("Type {$type} does not exist. Make sure the class is loaded.");
        }
    }
}
