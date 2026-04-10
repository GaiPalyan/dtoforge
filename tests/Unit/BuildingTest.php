<?php

declare(strict_types=1);

use Ru\One2Work\Php\DtoValidator\Exceptions\DtoValidationException;
use Tests\Stubs\NestedDto;
use Tests\Stubs\SimpleDto;
use Tests\Stubs\Validation\Simple\AtLeastOne;

describe('DtoBuilding', function () {
    describe('SimpleDto', function () {
        it('creates from array', function () {
            $data = ['name' => 'John Doe', 'age' => '25', 'address' => 'Some address'];
            $dto = new SimpleDto($data);

            expect($dto->name)->toBe('John Doe')
                ->and($dto->age)->toBe('25')
                ->and($dto->address)->toBe('Some address');
        });

        it('creates from json string', function () {
            $data = ['name' => 'John Doe', 'age' => '25', 'address' => 'Some address'];
            $dto = new SimpleDto(json_encode($data));

            expect($dto->name)->toBe('John Doe')
                ->and($dto->age)->toBe('25')
                ->and($dto->address)->toBe('Some address');
        });

        it('creates via setters and reads via getters', function () {
            $dto = new SimpleDto()
                ->setName('John Doe')
                ->setAge('25')
                ->setAddress('Some address');

            expect($dto->getName())->toBe('John Doe')
                ->and($dto->getAge())->toBe('25')
                ->and($dto->getAddress())->toBe('Some address');
        });

        it('fills from array and overwrites individual fields', function () {
            $dto = new SimpleDto();
            $dto->fill(['name' => 'John Doe', 'age' => '25', 'address' => 'Some address']);

            expect($dto->getName())->toBe('John Doe');

            $dto->fill(['name' => 'Some name']);

            expect($dto->name)->toBe('Some name')
                ->and($dto->getName())->toBe('Some name');
        });

        it('casts scalar to string on set', function () {
            $dto = new SimpleDto()->setAge(25);

            expect($dto->getAge())->toBeString();
        });

        it('serializes to array', function () {
            $data = ['name' => 'John Doe', 'age' => '25', 'address' => 'Some address'];

            expect((new SimpleDto($data))->toArray())->toBe($data);
        });

        it('serializes to json', function () {
            $data = ['name' => 'John Doe', 'age' => '25', 'address' => 'Some address'];

            expect((new SimpleDto($data))->toJson())->toBe(json_encode($data));
        });

        it('clones without differences', function () {
            $dto = new SimpleDto(['name' => 'John Doe', 'age' => '25', 'address' => 'Some address']);
            $clone = $dto->clone();

            expect($dto->diff($clone))->toBeEmpty();
        });

        it('merges two dtos into a new instance with combined fields', function () {
            $dto1 = new SimpleDto()->setName('John Doe');
            $dto2 = new SimpleDto()->setAge('25')->setAddress('Some address');
            $dto3 = $dto1->merge($dto2);

            expect($dto3->getName())->toBe('John Doe')
                ->and($dto3->getAge())->toBe('25')
                ->and($dto3->getAddress())->toBe('Some address');
        });

        it('diffs two dtos showing changed and missing fields', function () {
            $dto1 = new SimpleDto(['name' => 'John Doe', 'age' => '25', 'address' => 'Some address']);
            $dto2 = new SimpleDto(['name' => 'Some name']);
            $diff = $dto1->diff($dto2);

            expect($diff['name']['old'])->toBe('John Doe')
                ->and($diff['name']['new'])->toBe('Some name')
                ->and($diff['age']['old'])->toBe('25')
                ->and($diff['age']['new'])->toBeNull()
                ->and($diff['address']['old'])->toBe('Some address')
                ->and($diff['address']['new'])->toBeNull();
        });
    });

    describe('NestedDto', function () {
        it('creates with nested dto child', function () {
            $data = [
                'children' => new SimpleDto(['name' => 'John Doe', 'age' => '25', 'address' => 'Some address']),
                'companyAddress' => 'Some address',
                'companyName' => 'google',
            ];
            $dto = new NestedDto($data);

            expect($dto->getChildren())->toBeInstanceOf(SimpleDto::class)
                ->and($dto->getCompanyAddress())->toBe('Some address')
                ->and($dto->getCompanyName())->toBe('google');
        });

        it('sets nested dto child via setter', function () {
            $child = new SimpleDto(['name' => 'John Doe', 'age' => '25', 'address' => 'Some address']);
            $dto = new NestedDto()->setChildren($child);

            expect($dto->getChildren())->toBeInstanceOf(SimpleDto::class);
        });

        it('serializes nested dto to array and json', function () {
            $child = new SimpleDto(['name' => 'John Doe', 'age' => '25', 'address' => 'Some address']);
            $data = ['children' => $child, 'companyAddress' => 'Some address', 'companyName' => 'google'];
            $dto = new NestedDto($data);

            expect($dto->toArray())->toBe([
                'children' => $child->toArray(),
                'companyAddress' => 'Some address',
                'companyName' => 'google',
            ])->and($dto->toJson())->toBe(json_encode($data));
        });

        it('merges and overwrites nested child fields', function () {
            $child = new SimpleDto(['name' => 'John Doe', 'age' => '25', 'address' => 'Some address']);
            $dto1 = new NestedDto(['children' => $child, 'companyAddress' => 'Some address', 'companyName' => 'google']);
            $dto2 = new NestedDto();
            $dto2->setChildren($child->clone()->setName('Changed name')->setAge(null));

            $dto3 = $dto1->merge($dto2);
            $dto3Children = $dto3->getChildren();

            expect($dto3->getCompanyName())->toBe('google')
                ->and($dto3->getCompanyAddress())->toBe('Some address')
                ->and($dto3Children)->toBeInstanceOf(SimpleDto::class)
                ->and($dto3Children->getName())->toBe('Changed name')
                ->and($dto3Children->getAge())->toBeEmpty();
        });

        it('throws DtoValidationException when merged result violates a ClassValidator rule', function () {
            $dto1 = new AtLeastOne(); // no data — class validation bypassed for empty input
            $dto2 = new AtLeastOne(['name' => 'John'], lazyValidation: true); // lazy — class validation bypassed

            // Neither dto has field1 or field2 set, so the merged result violates AtLeastOne
            expect(fn () => $dto1->merge($dto2))
                ->toThrow(DtoValidationException::class);
        });
    });
})->group('Build');
