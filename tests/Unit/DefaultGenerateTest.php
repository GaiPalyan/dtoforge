<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\Stubs\GenerateDefaultDto;

describe('DtoBuilding', function () {
    it('generate default if value is null and support is true', function () {
        $dto = new GenerateDefaultDto(['value' => null]);
        $dto->generateDefaultsIfAllowed();

        expect($dto->getValue())->toBe('Generated value');
    });

    it('does not generate default if supports returns false', function () {
        $dto = new GenerateDefaultDto(['value' => 'already set']);
        $dto->generateDefaultsIfAllowed();

        expect($dto->getValue())->toBe('already set');
    });

    it('does not change value if generateDefaultsIfAllowed is not called', function () {
        $dto = new GenerateDefaultDto(['value' => null]);

        expect($dto->getValue())->toBeNull();
    });
})->group('DefaultGenerate');
