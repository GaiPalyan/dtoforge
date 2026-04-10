<?php
/*
 * Copyright 2021-2026 DATOP (ALTESSA SOLUTIONS) LLC. All rights reserved.
 * Use of this source code is governed by license that can be found in
 * the LICENSE file.
 */

declare(strict_types=1);

namespace Ru\One2Work\Php\DtoValidator\Internal;

use ReflectionNamedType;
use ReflectionProperty;
use Ru\One2Work\Php\DtoValidator\Contracts\ClassValidatorInterface;
use Ru\One2Work\Php\DtoValidator\Contracts\DefaultValueGeneratorInterface;
use Ru\One2Work\Php\DtoValidator\Contracts\PropertyMaskInterface;
use Ru\One2Work\Php\DtoValidator\Contracts\PropertyValidatorInterface;

final class Metadata
{
    /** @var array<string, self> */
    private static array $cache = [];

    /**
     * @param array<string, string> $propertyTypes
     * @param array<string, list<PropertyValidatorInterface>> $propertyValidator
     * @param array<string, PropertyMaskInterface> $propertyMask
     * @param array<string, DefaultValueGeneratorInterface> $defaultValueGenerators
     * @param array<string, string|null> $arrayItemTypes
     * @param list<ClassValidatorInterface> $classValidator
     */
    private function __construct(
        public readonly array $propertyTypes,
        public readonly array $propertyValidator,
        public readonly array $propertyMask,
        public readonly array $defaultValueGenerators,
        public readonly array $arrayItemTypes,
        public readonly array $classValidator,
    ) {}

    public static function for(string $class): self
    {
        return self::$cache[$class] ??= self::build($class);
    }

    private static function build(string $class): self
    {
        $propertyTypes = [];
        $propertyValidator = [];
        $propertyMask = [];
        $defaultValueGenerators = [];
        $arrayItemTypes = [];
        $classValidator = [];

        $reflection = new \ReflectionClass($class);

        foreach ($reflection->getAttributes() as $attribute) {
            if (($instance = $attribute->newInstance()) instanceof ClassValidatorInterface) {
                $classValidator[] = $instance;
            }
        }

        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if (! ($type = $property->getType()) instanceof ReflectionNamedType) {
                continue;
            }

            $name = $property->getName();
            $typeName = $type->getName();

            $propertyTypes[$name] = $typeName;

            foreach ($property->getAttributes() as $attribute) {
                $instance = $attribute->newInstance();

                if ($instance instanceof PropertyValidatorInterface) {
                    $propertyValidator[$name][] = $instance;
                }
                if ($instance instanceof PropertyMaskInterface && ! isset($propertyMask[$name])) {
                    $propertyMask[$name] = $instance;
                }
                if ($instance instanceof DefaultValueGeneratorInterface && ! isset($defaultValueGenerators[$name])) {
                    $defaultValueGenerators[$name] = $instance;
                }
            }

            if ($typeName === 'array') {
                $arrayItemTypes[$name] = preg_match('/@var\s+([^\s]+)\[\]/', (string) $property->getDocComment(), $matches)
                    ? $matches[1]
                    : null;
            }
        }

        return new self(
            propertyTypes: $propertyTypes,
            propertyValidator: $propertyValidator,
            propertyMask: $propertyMask,
            defaultValueGenerators: $defaultValueGenerators,
            arrayItemTypes: $arrayItemTypes,
            classValidator: $classValidator,
        );
    }
}
