<?php

declare(strict_types=1);

namespace Forge\Dto\Support;

final readonly class Violation
{
    public function __construct(
        public string $fieldPath,
        public string $message,
        public string $rule,
    ) {}

    /** @return array{field_path: string, message: string, rule: string} */
    public function toArray(): array
    {
        return [
            'field_path' => $this->fieldPath,
            'message' => $this->message,
            'rule' => $this->rule,
        ];
    }
}
