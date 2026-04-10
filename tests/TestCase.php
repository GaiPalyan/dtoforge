<?php

declare(strict_types=1);

namespace Tests;

use Ru\One2Work\Php\DtoValidator\DtoValidatorServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            DtoValidatorServiceProvider::class,
        ];
    }
}
