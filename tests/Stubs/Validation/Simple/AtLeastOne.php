<?php

declare(strict_types=1);

namespace Tests\Stubs\Validation\Simple;

use Forge\Dto\BaseDto;
use Forge\Dto\Support\Validation\AtLeastOne as AtLeastOneValidator;

/**
 * @method self setName(?string $name)
 * @method self setField1(?string $field1)
 * @method self setField2(?string $field2)
 */
#[AtLeastOneValidator(['field1', 'field2'])]
final class AtLeastOne extends BaseDto
{
    public ?string $name = null;

    public ?string $field1 = null;

    public ?string $field2 = null;
}
