<?php

declare(strict_types=1);

use PHPUnit\Framework\AssertionFailedError;
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
        // Search right after creating, before the rename below: Billingo indexes partners for searching
        // with a delay, and a renamed partner may not be found by its old name until it is reindexed.
        sandboxEventually(function () use ($partner, $id): void {
            $query = (string) $partner->name;
            $found = Billingo::partners()->lazy(query: $query)
                ->mapWithKeys(fn (PartnerData $result): array => [(int) $result->id => $result->name])
                ->all();

            if (! array_key_exists($id, $found)) {
                // Describe the first page, so a failure shows whether the query filters and whether pagination continues.
                $page = Billingo::partners()->list(page: 1, perPage: 100, query: $query);

                $this->fail(sprintf(
                    'Searching for "%s" did not return partner %d. lazy() returned %d partners. First page: total=%d, per_page=%d, current_page=%d, last_page=%d, count=%d, first names: %s',
                    $query,
                    $id,
                    count($found),
                    $page->total,
                    $page->perPage,
                    $page->currentPage,
                    $page->lastPage,
                    $page->count(),
                    json_encode($page->data->take(5)->pluck('name')->all(), JSON_UNESCAPED_UNICODE),
                ));
            }
        }, AssertionFailedError::class, 60);

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
    } finally {
        Billingo::partners()->delete($id);
    }

    expect(fn () => Billingo::partners()->get($id))->toThrow(BillingoException::class);
});
