<?php

declare(strict_types=1);

use Szabrob\LaravelBillingo\Data\Products\ProductData;
use Szabrob\LaravelBillingo\Enums\Currency;
use Szabrob\LaravelBillingo\Enums\Vat;
use Szabrob\LaravelBillingo\Exceptions\BillingoException;
use Szabrob\LaravelBillingo\Facades\Billingo;

beforeEach(fn () => sandbox());

it('creates, reads, updates, lists and deletes a product', function () {
    $name = sandboxName('product');

    $product = Billingo::products()->create(new ProductData(
        name: $name,
        currency: Currency::HUF,
        vat: Vat::Percent27,
        unit: 'db',
        netUnitPrice: 1000,
    ));
    $id = (int) $product->id;

    try {
        $fetched = Billingo::products()->get($id);

        expect($fetched->name)->toBe($name)
            ->and($fetched->currency)->toBe(Currency::HUF)
            ->and($fetched->vat)->toBe(Vat::Percent27)
            ->and($fetched->netUnitPrice)->toEqual(1000);

        $updated = Billingo::products()->update($id, new ProductData(
            name: $name.' updated',
            currency: Currency::HUF,
            vat: Vat::Percent27,
            unit: 'db',
            netUnitPrice: 2000,
        ));

        expect($updated->name)->toBe($name.' updated')
            ->and($updated->netUnitPrice)->toEqual(2000);

        $ids = Billingo::products()->lazy(query: $name)->map(fn (ProductData $found): ?int => $found->id);

        expect($ids->all())->toContain($id);
    } finally {
        Billingo::products()->delete($id);
    }

    expect(fn () => Billingo::products()->get($id))->toThrow(BillingoException::class);
});
