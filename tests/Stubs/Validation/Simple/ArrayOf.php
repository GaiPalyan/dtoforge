<?php

declare(strict_types=1);

namespace Tests\Stubs\Validation\Simple;

use Ru\One2Work\Php\DtoValidator\BaseDto;
use Ru\One2Work\Php\DtoValidator\Support\Validation\ArrayOf as ArrayOfValidator;

/**
 * @method self setItems(array<int, \stdClass>|null $items)
 */
final class ArrayOf extends BaseDto
{
    /** @var array<int, \stdClass>|null */
    #[ArrayOfValidator(\stdClass::class)]
    public ?array $items = null;
}
