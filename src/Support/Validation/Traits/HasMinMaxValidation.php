<?php

declare(strict_types=1);

namespace Forge\Dto\Support\Validation\Traits;

trait HasMinMaxValidation
{
    protected function validateMinMax(?int $min, ?int $max, string $prefix = ''): void
    {
        $maxName = $prefix ? $prefix . 'max' : 'max';
        $minName = $prefix ? $prefix . 'min' : 'min';

        if ($max !== null && $max <= 0) {
            throw new \InvalidArgumentException("{$maxName} must be positive number");
        }

        if ($min !== null && $min < 0) {
            throw new \InvalidArgumentException("{$minName} cannot be negative");
        }

        if ($max !== null && $min !== null && $max < $min) {
            throw new \InvalidArgumentException("{$maxName} cannot be less than {$minName}");
        }
    }
}
