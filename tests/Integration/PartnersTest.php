<?php

declare(strict_types=1);

use Szabrob\LaravelBillingo\Data\Partners\PartnerData;
use Szabrob\LaravelBillingo\Data\Shared\AddressData;
use Szabrob\LaravelBillingo\Enums\Country;
use Szabrob\LaravelBillingo\Enums\PartnerTaxType;
use Szabrob\LaravelBillingo\Exceptions\BillingoException;
use Szabrob\LaravelBillingo\Facades\Billingo;

beforeEach(fn () => sandbox());

it('creates, reads, updates, lists and deletes a partner', function () {
    $partner = sandboxPartner();
    $id = (int) $partner->id;

    try {
        $fetched = Billingo::partners()->get($id);

        expect($fetched->name)->toBe($partner->name)
            ->and($fetched->address?->countryCode)->toBe(Country::HU)
            ->and($fetched->address?->city)->toBe('Budapest')
            ->and($fetched->taxType)->toBe(PartnerTaxType::NoTaxNumber);

        $updated = Billingo::partners()->update($id, new PartnerData(
            name: $partner->name.' updated',
            address: new AddressData(countryCode: Country::HU, postCode: '1111', city: 'Budapest', address: 'Teszt utca 2.'),
            emails: [],
            taxType: PartnerTaxType::NoTaxNumber,
        ));

        expect($updated->name)->toBe($partner->name.' updated')
            ->and($updated->address?->address)->toBe('Teszt utca 2.');

        $ids = Billingo::partners()->lazy(query: (string) $partner->name)->map(fn (PartnerData $found): ?int => $found->id);

        expect($ids->all())->toContain($id);
    } finally {
        Billingo::partners()->delete($id);
    }

    expect(fn () => Billingo::partners()->get($id))->toThrow(BillingoException::class);
});
