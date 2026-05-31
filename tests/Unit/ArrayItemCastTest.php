<?php

declare(strict_types=1);

use Tests\Stubs\Collection\CollectionDto;
use Tests\Stubs\Collection\CollectionItemDto;

describe('ArrayItemCast', function () {
    it('casts array items to objects declared via the #[CastEachTo] attribute', function () {
        $dto = new CollectionDto([
            'items' => [
                ['label' => 'first'],
                ['label' => 'second'],
            ],
        ]);

        expect($dto->items)->toBeArray()
            ->and($dto->items)->toHaveCount(2)
            ->and($dto->items[0])->toBeInstanceOf(CollectionItemDto::class)
            ->and($dto->items[0]->label)->toBe('first')
            ->and($dto->items[1])->toBeInstanceOf(CollectionItemDto::class)
            ->and($dto->items[1]->label)->toBe('second');
    });
})->group('Build');
