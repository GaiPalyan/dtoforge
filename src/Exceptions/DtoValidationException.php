<?php

declare(strict_types=1);

namespace Ru\One2Work\Php\DtoValidator\Exceptions;

use Ru\One2Work\Php\DtoValidator\Support\Violation;

class DtoValidationException extends \RuntimeException
{
    /** @param Violation[] $violations */
    public function __construct(private readonly array $violations)
    {
        parent::__construct('DTO validation failed');
    }

    /** @return Violation[] */
    public function getViolations(): array
    {
        return $this->violations;
    }
}
