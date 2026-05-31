<?php

declare(strict_types=1);

namespace Tests\Stubs\Mask;

use Forge\Dto\BaseDto;

/**
 * @method string|null getCardNumber()
 * @method string|null getHolder()
 */
final class MaskedDto extends BaseDto
{
    #[SimpleMask]
    public ?string $cardNumber = null;

    public ?string $holder = null;
}
