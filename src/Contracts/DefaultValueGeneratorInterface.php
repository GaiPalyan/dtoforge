<?php

declare(strict_types=1);

namespace Forge\Dto\Contracts;

use Forge\Dto\BaseDto;

/**
 * Interface for default value generators for DTO properties.
 *
 * Generators define the logic by which a DTO property can be automatically filled
 * when not explicitly set, based on the policy defined in the supports() method.
 */
interface DefaultValueGeneratorInterface
{
    public function supports(BaseDto $dto, string $propertyName): bool;

    public function generate(BaseDto $dto): ?string;
}
