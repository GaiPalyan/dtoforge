<?php

declare(strict_types=1);

namespace Tests\Stubs\Mask;

use Attribute;
use Forge\Dto\Contracts\PropertyMaskInterface;

#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class SimpleMask implements PropertyMaskInterface
{
    public function apply(string $value): string
    {
        return str_repeat('*', max(0, strlen($value) - 4)) . substr($value, -4);
    }
}
