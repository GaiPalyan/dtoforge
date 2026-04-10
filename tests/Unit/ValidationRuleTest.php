<?php

declare(strict_types=1);

use Forge\Dto\Exceptions\DtoValidationException;
use Tests\Stubs\Validation\Simple\ArrayOf;
use Tests\Stubs\Validation\Simple\AtLeastOne;
use Tests\Stubs\Validation\Simple\Required;

describe('DtoValidation', function () {
    it('required pass', function (mixed $value) {
        expect(fn () => new Required()->setValue($value))->not->toThrow(DtoValidationException::class);
    })->with([
        'John Doe',
        '11111',
        11111,
        false,
    ]);

    it('required failed', function (mixed $value) {
        expect(fn () => new Required()->setValue($value))->toThrow(DtoValidationException::class);
    })->with([
        '',
        null,
    ]);

    it('array of pass', function (array $items) {
        expect(fn () => new ArrayOf()->setItems($items))->not->toThrow(DtoValidationException::class);
    })->with([
        'empty array' => [[]],
        'array with stdClass' => [[new stdClass()]],
    ]);

    it('array of failed', function (mixed $items) {
        expect(fn () => new ArrayOf()->setItems($items))->toThrow(DtoValidationException::class);
    })->with([
        'string array' => [['not', 'stdclass']],
        'integer array' => [[1, 2, 3]],
        'mixed array' => [[new stdClass(), 'string']],
    ]);

    it('at least one pass', function (array $data) {
        $dto = new AtLeastOne();
        if (isset($data['field1'])) {
            $dto->setField1($data['field1']);
        }

        if (isset($data['field2'])) {
            $dto->setField2($data['field2']);
        }

        expect($dto)->not->toThrow(DtoValidationException::class);
    })->with([
        'field1 only' => [['field1' => 'value']],
        'field2 only' => [['field2' => 'value']],
        'both fields' => [['field1' => 'value1', 'field2' => 'value2']],
    ]);

    it('at least one failed', function () {
        expect(fn () => new AtLeastOne(['name' => 'value']))->toThrow(DtoValidationException::class);
    });
})->group('Validate');
