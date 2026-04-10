<?php

declare(strict_types=1);

namespace Ru\One2Work\Php\DtoValidator\Internal;

use DateTimeInterface;
use Ru\One2Work\Php\DtoValidator\BaseDto;

final class Serializer
{
    public function __construct(private readonly Metadata $metadata) {}

    /** @return array<string, mixed> */
    public function toArray(BaseDto $dto, bool $clearing = false, bool $masking = false): array
    {
        $result = [];

        foreach ($this->getProperties($dto) as $key => $value) {
            $value = match (true) {
                $value instanceof BaseDto => $value->toArray($clearing, $masking),
                $value instanceof DateTimeInterface => $value->format(DateTimeInterface::RFC3339),
                is_array($value) => array_map(
                    static fn ($item) => $item instanceof BaseDto ? $item->toArray($clearing, $masking) : $item,
                    $value
                ),
                default => $value,
            };

            $result[$key] = $this->maskValue($key, $value, $masking);
        }

        return $clearing ? array_filter($result, fn ($value) => $value !== null) : $result;
    }

    public function toJson(BaseDto $dto, int $options = JSON_UNESCAPED_UNICODE, bool $clearing = true, bool $masking = false): ?string
    {
        $data = $this->toArray($dto, $clearing, $masking);

        return $data ? json_encode($data, $options) ?: null : null;
    }

    /** @return array<string, mixed> */
    private function getProperties(BaseDto $dto): array
    {
        return array_intersect_key(get_object_vars($dto), $this->metadata->propertyTypes);
    }

    private function maskValue(string $key, mixed $value, bool $masking): mixed
    {
        if ($masking && isset($this->metadata->propertyMask[$key]) && is_string($value)) {
            return $this->metadata->propertyMask[$key]->apply($value);
        }

        return $value;
    }
}
