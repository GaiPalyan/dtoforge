<?php

declare(strict_types=1);

namespace Forge\Dto\Support\Validation;

use Attribute;
use Illuminate\Support\Arr;
use Forge\Dto\BaseDto;
use Forge\Dto\Contracts\ClassValidatorInterface;
use Forge\Dto\Support\Validation\Traits\HasLaravelValidation;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class AtLeastOne implements ClassValidatorInterface
{
    use HasLaravelValidation;

    /** @param list<string> $atLeastOneRequired */
    public function __construct(public array $atLeastOneRequired) {}

    public function validate(BaseDto $dto): void
    {
        $data = Arr::only($dto->toArray(), $this->atLeastOneRequired);

        if (empty(array_filter($data, static fn ($v) => ! is_null($v)))) {
            $this->performValidation(
                value: null,
                rules: ['required'],
                field: Arr::first($this->atLeastOneRequired),
                message: 'At least one of the following fields must be filled: ' . implode(', ', $this->atLeastOneRequired)
            );
        }
    }
}
