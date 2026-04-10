<?php
/*
 * Copyright 2021-2026 DATOP (ALTESSA SOLUTIONS) LLC. All rights reserved.
 * Use of this source code is governed by license that can be found in
 * the LICENSE file.
 */

declare(strict_types=1);

namespace Ru\One2Work\Php\DtoValidator\Support;

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
