<?php

declare(strict_types=1);

namespace Ru\One2Work\Php\DtoValidator\Contracts;

use Ru\One2Work\Php\DtoValidator\BaseDto;
use Ru\One2Work\Php\DtoValidator\Exceptions\DtoValidationException;

interface ClassValidatorInterface
{
    /** @throws DtoValidationException */
    public function validate(BaseDto $dto): void;
}
