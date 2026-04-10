<?php
/*
 * Copyright 2021-2026 DATOP (ALTESSA SOLUTIONS) LLC. All rights reserved.
 * Use of this source code is governed by license that can be found in
 * the LICENSE file.
 */

declare(strict_types=1);

namespace Ru\One2Work\Php\DtoValidator\Support\Validation;

use Attribute;
use Illuminate\Support\Arr;
use Ru\One2Work\Php\DtoValidator\BaseDto;
use Ru\One2Work\Php\DtoValidator\Contracts\ClassValidatorInterface;
use Ru\One2Work\Php\DtoValidator\Support\Validation\Traits\HasLaravelValidation;

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
