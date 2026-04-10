<?php

declare(strict_types=1);

namespace Tests\Stubs;

use Attribute;
use Forge\Dto\BaseDto;
use Forge\Dto\Contracts\DefaultValueGeneratorInterface;

#[Attribute(Attribute::TARGET_PROPERTY)]
class SimpleDefaultValueGenerator implements DefaultValueGeneratorInterface
{
    public function __construct() {}

    public function generate(BaseDto $dto): ?string
    {
        return 'Generated value';
    }

    public function supports(BaseDto $dto, string $propertyName): bool
    {
        $value = $dto->{$propertyName} ?? null;

        return empty($dto->getValidationErrors()) && ($value === null || $value === '');
    }
}
