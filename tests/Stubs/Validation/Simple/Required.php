<?php

declare(strict_types=1);

namespace Tests\Stubs\Validation\Simple;

use Ru\One2Work\Php\DtoValidator\BaseDto;
use Ru\One2Work\Php\DtoValidator\Support\Validation\Required as RequiredValidator;

/**
 * @method self setValue(mixed $value)
 * @method mixed getValue($value)
 */
final class Required extends BaseDto
{
    #[RequiredValidator]
    public mixed $value = null;
}
