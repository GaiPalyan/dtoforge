<?php

declare(strict_types=1);

namespace Forge\Dto\Contracts;

interface PropertyMaskInterface
{
    public function apply(string $value): string;
}
