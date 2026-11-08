<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Szabrob\LaravelBillingo\Data\Products\ProductData;
use Szabrob\LaravelBillingo\Enums\Currency;
use Szabrob\LaravelBillingo\Enums\Vat;
use Szabrob\LaravelBillingo\Facades\Billingo;

beforeEach(fn () => Http::preventStrayRequests());

$productResponse = [
    'id' => 9,
    'name' => 'Tanácsadás',
    'comment' => '',
    'currency' => 'HUF',
    'vat' => '27%',
    'net_unit_price' => 10000,
    'gross_unit_price' => 12700,
    'unit' => 'óra',
];

it('lists products', function () use ($productResponse) {
    Http::fake(['*' => Http::response(['data' => [$productResponse], 'total' => 1, 'per_page' => 25, 'current_page' => 1, 'last_page' => 1])]);

    $page = Billingo::products()->list(query: 'Tan');

    assertBillingoRequest('GET', 'products?page=1&per_page=25&query=Tan');
    expect($page->data->first()?->vat)->toBe(Vat::Percent27)
        ->and($page->hasMorePages())->toBeFalse();
});

it('creates, reads, updates and deletes products', function () use ($productResponse) {
    Http::fake([
        'api.billingo.hu/v3/products' => Http::response($productResponse, 201),
        'api.billingo.hu/v3/products/9' => Http::sequence()
            ->push($productResponse)
            ->push($productResponse)
            ->push(null, 204),
    ]);
    $product = new ProductData(name: 'Tanácsadás', currency: Currency::HUF, vat: Vat::Percent27, unit: 'óra', netUnitPrice: 10000);

    $created = Billingo::products()->create($product);
    $fetched = Billingo::products()->get(9);
    Billingo::products()->update(9, $product);
    Billingo::products()->delete(9);

    assertBillingoRequest('POST', 'products', [
        'name' => 'Tanácsadás',
        'currency' => 'HUF',
        'vat' => '27%',
        'unit' => 'óra',
        'net_unit_price' => 10000.0,
    ]);
    assertBillingoRequest('PUT', 'products/9');
    assertBillingoRequest('DELETE', 'products/9');
    expect($created->id)->toBe(9)
        ->and($fetched->grossUnitPrice)->toBe(12700.0);
});
