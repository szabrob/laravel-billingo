<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Exceptions\CannotCastEnum;
use Szabrob\LaravelBillingo\Data\Documents\DocumentData;
use Szabrob\LaravelBillingo\Data\Documents\DocumentSettingsData;
use Szabrob\LaravelBillingo\Data\Organization\SubscriptionData;
use Szabrob\LaravelBillingo\Data\Partners\PartnerData;
use Szabrob\LaravelBillingo\Data\Shared\AddressData;
use Szabrob\LaravelBillingo\Enums\Country;
use Szabrob\LaravelBillingo\Enums\Feature;
use Szabrob\LaravelBillingo\Enums\Round;

it('omits unset values from nested payloads', function () {
    $partner = new PartnerData(
        name: 'Teszt Kft.',
        address: new AddressData(countryCode: Country::HU, postCode: '1111', city: 'Budapest', address: 'Fő utca 1.'),
    );

    expect($partner->toPayload())->toBe([
        'name' => 'Teszt Kft.',
        'address' => ['country_code' => 'HU', 'post_code' => '1111', 'city' => 'Budapest', 'address' => 'Fő utca 1.'],
    ]);
});

it('keeps false and empty string values in payloads', function () {
    $settings = new DocumentSettingsData(mediatedService: false, round: Round::None, orderNumber: '');

    expect($settings->toPayload())->toBe(['mediated_service' => false, 'round' => 'none', 'order_number' => '']);
});

it('casts billingo dates and treats empty dates as missing', function () {
    $document = DocumentData::from(['invoice_date' => '2026-11-08', 'paid_date' => '']);

    expect($document->invoiceDate)->toBeInstanceOf(CarbonImmutable::class)
        ->and($document->invoiceDate?->toDateString())->toBe('2026-11-08')
        ->and($document->paidDate)->toBeNull()
        ->and($document->toPayload())->toMatchArray(['invoice_date' => '2026-11-08']);
});

it('accepts date objects when creating data from arrays', function () {
    $document = DocumentData::from(['due_date' => new DateTime('2026-12-01')]);

    expect($document->dueDate?->toDateString())->toBe('2026-12-01');
});

it('casts and transforms enum lists', function () {
    $subscription = SubscriptionData::from(['features' => ['inventory', Feature::Softpos]]);

    expect($subscription->features)->toBe([Feature::Inventory, Feature::Softpos])
        ->and($subscription->toPayload())->toBe(['features' => ['inventory', 'softpos']])
        ->and(SubscriptionData::from(['features' => null])->features)->toBeNull();
});

it('fails loudly on values that are not part of the billingo spec', function () {
    DocumentData::from(['type' => 'unknown-type']);
})->throws(CannotCastEnum::class);
