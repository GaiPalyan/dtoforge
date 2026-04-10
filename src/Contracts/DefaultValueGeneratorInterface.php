<?php

declare(strict_types=1);

namespace Ru\One2Work\Php\DtoValidator\Contracts;

use Ru\One2Work\Php\DtoValidator\BaseDto;

/**
 * Интерфейс генераторов значений по умолчанию для DTO-свойств.
 *
 * Генераторы реализуют логику, по которой свойство DTO может быть автоматически заполнено,
 * если оно не задано явно, опирается на политику метода supports().
 *
 * Example
 *
 * ```php
 * $dto = new RowImportCivilLawContractDto([
 *     'contractNumber' => 'ГПХ-123',
 *     'contractDate' => '2025-06-13',
 * ]);
 *
 * $dto->generateDefaultProperty('paymentPurpose');
 *
 * echo $dto->getPaymentPurpose();
 * // result: "Оплата по договору №ГПХ-123 от 13.06.2025. Без НДС."
 * ```
 */
interface DefaultValueGeneratorInterface
{
    public function supports(BaseDto $dto, string $propertyName): bool;

    public function generate(BaseDto $dto): ?string;
}
