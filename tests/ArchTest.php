<?php

declare(strict_types=1);

use Szabrob\LaravelBillingo\Data\BillingoData;
use Szabrob\LaravelBillingo\Resources\Resource;

arch()->preset()->php();

arch()->preset()->security();

arch('it will not use dd(), ddd(), env(), or exit()')
    ->expect(['dd', 'ddd', 'env', 'exit'])
    ->each->not->toBeUsed();

arch('the package source declares strict types')
    ->expect('Szabrob\LaravelBillingo')
    ->toUseStrictTypes();

arch('data objects extend the package base data class')
    ->expect('Szabrob\LaravelBillingo\Data')
    ->classes()
    ->toExtend(BillingoData::class)
    ->toHaveSuffix('Data')
    ->ignoring(BillingoData::class);

arch('enums are string backed')
    ->expect('Szabrob\LaravelBillingo\Enums')
    ->toBeStringBackedEnums();

arch('resources are final and extend the base resource')
    ->expect('Szabrob\LaravelBillingo\Resources')
    ->classes()
    ->toExtend(Resource::class)
    ->toBeFinal()
    ->ignoring(Resource::class);
