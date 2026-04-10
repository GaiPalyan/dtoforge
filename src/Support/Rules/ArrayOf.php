<?php

declare(strict_types=1);

namespace Forge\Dto\Support\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final readonly class ArrayOf implements ValidationRule
{
    public function __construct(private string $type) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_null($value)) {
            return;
        }

        if (! is_array($value)) {
            $fail("The {$attribute} must be an array");
        }

        foreach ($value as $index => $item) {
            if (! $item instanceof $this->type) {
                $given = get_debug_type($item);
                $fail("Item at index {$index} in {$attribute} must be an instance of {$this->type}, {$given} given");
            }
        }
    }
}
