<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Szabrob\LaravelBillingo\Data\Partners\PartnerData;
use Szabrob\LaravelBillingo\Data\Shared\AddressData;
use Szabrob\LaravelBillingo\Enums\Country;
use Szabrob\LaravelBillingo\Enums\Currency;
use Szabrob\LaravelBillingo\Enums\PartnerTaxType;
use Szabrob\LaravelBillingo\Enums\PaymentMethod;
use Szabrob\LaravelBillingo\Facades\Billingo;

beforeEach(fn () => Http::preventStrayRequests());

function newPartner(): PartnerData
{
    return new PartnerData(
        name: 'Teszt Kft.',
        address: new AddressData(countryCode: Country::HU, postCode: '1111', city: 'Budapest', address: 'Fő utca 1.'),
        emails: ['szamla@teszt.hu'],
        taxcode: '12345678-2-42',
        taxType: PartnerTaxType::HasTaxNumber,
    );
}

it('lists partners with pagination metadata', function () {
    Http::fake(['*' => Http::response([
        'data' => [billingoFixture('partner')],
        'total' => 51,
        'per_page' => 25,
        'current_page' => 2,
        'last_page' => 3,
    ])]);

    $page = Billingo::partners()->list(page: 2, query: 'Teszt');

    assertBillingoRequest('GET', 'partners?page=2&per_page=25&query=Teszt');
    expect($page->data)->toHaveCount(1)
        ->and($page->data->first())->toBeInstanceOf(PartnerData::class)
        ->and($page->total)->toBe(51)
        ->and($page->currentPage)->toBe(2)
        ->and($page->hasMorePages())->toBeTrue()
        ->and($page)->toHaveCount(1);
});

it('lazily iterates over every page', function () {
    Http::fakeSequence()
        ->push(['data' => [billingoFixture('partner'), billingoFixture('partner')], 'total' => 3, 'per_page' => 2, 'current_page' => 1, 'last_page' => 2])
        ->push(['data' => [billingoFixture('partner')], 'total' => 3, 'per_page' => 2, 'current_page' => 2, 'last_page' => 2]);

    $partners = Billingo::partners()->lazy(perPage: 2)->all();

    expect($partners)->toHaveCount(3)->each->toBeInstanceOf(PartnerData::class)
        ->and(array_keys($partners))->toBe([0, 1, 2]);
    Http::assertSentCount(2);
    assertBillingoRequest('GET', 'partners?page=2&per_page=2');
});

it('maps a partner response to data objects', function () {
    Http::fake(['*' => Http::response(billingoFixture('partner'))]);

    $partner = Billingo::partners()->get(42);

    assertBillingoRequest('GET', 'partners/42');
    expect($partner->id)->toBe(42)
        ->and($partner->address?->countryCode)->toBe(Country::HU)
        ->and($partner->taxType)->toBe(PartnerTaxType::HasTaxNumber)
        ->and($partner->customBillingSettings?->paymentMethod)->toBe(PaymentMethod::WireTransfer)
        ->and($partner->customBillingSettings?->documentCurrency)->toBe(Currency::HUF)
        ->and($partner->groupMemberTaxNumber)->toBeNull();
});

it('creates a partner without sending unset fields', function () {
    Http::fake(['*' => Http::response(billingoFixture('partner'), 201)]);

    $partner = Billingo::partners()->create(newPartner());

    assertBillingoRequest('POST', 'partners', [
        'name' => 'Teszt Kft.',
        'address' => ['country_code' => 'HU', 'post_code' => '1111', 'city' => 'Budapest', 'address' => 'Fő utca 1.'],
        'emails' => ['szamla@teszt.hu'],
        'taxcode' => '12345678-2-42',
        'tax_type' => 'HAS_TAX_NUMBER',
    ]);
    expect($partner->id)->toBe(42);
});

it('updates, guesses and deletes partners', function () {
    Http::fake([
        'api.billingo.hu/v3/partners/42' => Http::sequence()
            ->push(billingoFixture('partner'))
            ->push(null, 204),
        'api.billingo.hu/v3/partners/guess' => Http::response(billingoFixture('partner'), 201),
    ]);

    expect(Billingo::partners()->update(42, newPartner())->id)->toBe(42)
        ->and(Billingo::partners()->guess(newPartner())->id)->toBe(42);
    Billingo::partners()->delete(42);

    assertBillingoRequest('PUT', 'partners/42');
    assertBillingoRequest('POST', 'partners/guess');
    assertBillingoRequest('DELETE', 'partners/42');
});
