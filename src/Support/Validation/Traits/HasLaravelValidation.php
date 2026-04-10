<?php
/*
 * Copyright 2021-2026 DATOP (ALTESSA SOLUTIONS) LLC. All rights reserved.
 * Use of this source code is governed by license that can be found in
 * the LICENSE file.
 */

declare(strict_types=1);

namespace Ru\One2Work\Php\DtoValidator\Support\Validation\Traits;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Ru\One2Work\Php\DtoValidator\Exceptions\DtoValidationException;
use Ru\One2Work\Php\DtoValidator\Support\Violation;

trait HasLaravelValidation
{
    /**
     * Perform validation using Laravel validator
     *
     * @param list<string|object> $rules
     * @throws DtoValidationException
     */
    protected function performValidation(
        mixed $value,
        array $rules,
        ?string $field = null,
        ?string $message = null
    ): void {
        $field = $field ?? 'value';

        $validator = Validator::make(
            [$field => $value],
            [$field => $rules]
        );

        if ($validator->fails()) {
            $violations = $this->makeViolations(
                errors: $validator->errors(),
                rules: $validator->failed(),
                message: $message
            );

            throw new DtoValidationException($violations);
        }
    }

    /**
     * Build validation rules for min/max constraints
     *
     * @return list<string>
     */
    protected function buildMinMaxRules(string $baseRule, mixed $min = null, mixed $max = null): array
    {
        $rules = [$baseRule];

        if ($min !== null) {
            $rules[] = match ($baseRule) {
                'integer', 'numeric' => "gte:{$min}",
                default => "min:{$min}"
            };
        }

        if ($max !== null) {
            $rules[] = match ($baseRule) {
                'integer', 'numeric' => "lte:{$max}",
                default => "max:{$max}"
            };
        }

        return $rules;
    }

    /**
     * @param array<string, array<string, array<mixed>>> $rules
     * @return Violation[]
     */
    private function makeViolations(MessageBag $errors, array $rules, ?string $message = ''): array
    {
        $violations = [];

        foreach ($rules as $field => $failedRules) {
            foreach (array_keys($failedRules) as $failedRule) {
                $violations[] = new Violation(
                    fieldPath: $field,
                    message: $message ?? $errors->first($field),
                    rule: $failedRule,
                );
            }
        }

        return $violations;
    }
}
