<?php

declare(strict_types=1);

namespace Ru\One2Work\Php\DtoValidator\Contracts;

interface PropertyMaskInterface
{
    public function apply(string $value): string;
}
