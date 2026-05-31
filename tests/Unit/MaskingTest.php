<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\Stubs\Mask\MaskedDto;

describe('Masking', function () {
    it('masks the masked property when masking is enabled', function () {
        $dto = new MaskedDto(['cardNumber' => '4111111111111111', 'holder' => 'John Doe']);

        expect($dto->toArray(masking: true))->toBe([
            'cardNumber' => '************1111',
            'holder' => 'John Doe',
        ]);
    });

    it('does not mask when masking is disabled', function () {
        $dto = new MaskedDto(['cardNumber' => '4111111111111111', 'holder' => 'John Doe']);

        expect($dto->toArray())->toBe([
            'cardNumber' => '4111111111111111',
            'holder' => 'John Doe',
        ]);
    });

    it('does not mutate the stored value', function () {
        $dto = new MaskedDto(['cardNumber' => '4111111111111111']);
        $dto->toArray(masking: true);

        expect($dto->cardNumber)->toBe('4111111111111111');
    });

    it('masks in json serialization', function () {
        $dto = new MaskedDto(['cardNumber' => '4111111111111111', 'holder' => 'John Doe']);

        expect($dto->toJson(masking: true))->toBe(json_encode([
            'cardNumber' => '************1111',
            'holder' => 'John Doe',
        ], JSON_UNESCAPED_UNICODE));
    });
})->group('Masking');
