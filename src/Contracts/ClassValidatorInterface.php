<?php

declare(strict_types=1);

namespace Forge\Dto\Contracts;

use Forge\Dto\BaseDto;
use Forge\Dto\Exceptions\DtoValidationException;

interface ClassValidatorInterface
{
    /** @throws DtoValidationException */
    public function validate(BaseDto $dto): void;
}
