<?php

declare(strict_types=1);

use Szabrob\LaravelBillingo\Enums\Currency;
use Szabrob\LaravelBillingo\Enums\TaxNumberCheckResult;
use Szabrob\LaravelBillingo\Facades\Billingo;

beforeEach(fn () => sandbox());

it('reads the organization', function () {
    $organization = Billingo::organization()->get();

    expect($organization->taxCode)->toBeString()->not->toBeEmpty()
        ->and($organization->hasNavConnection)->toBeBool();
});

it('reads the server time', function () {
    expect(Billingo::utils()->serverTime()->epoch)->toBeGreaterThan(0);
});

it('reads a currency conversion rate', function () {
    $rate = Billingo::currencies()->conversionRate(Currency::EUR, Currency::HUF);

    expect($rate->fromCurrency)->toBe(Currency::EUR)
        ->and($rate->toCurrency)->toBe(Currency::HUF)
        ->and($rate->conversationRate)->toBeGreaterThan(0);
});

it('checks a tax number', function () {
    $organization = Billingo::organization()->get();
    $taxCode = $organization->taxCode;

    // Billingo: "To use this feature, please ensure you have a live connection to NAV (not available in sandbox mode)"
    if ($organization->hasNavConnection !== true) {
        $this->markTestSkipped('Tax number checks need a live NAV connection, which Billingo sandbox accounts do not have.');
    }

    if ($taxCode === null || $taxCode === '') {
        $this->markTestSkipped('The Billingo test account has no tax code.');
    }

    expect(Billingo::utils()->checkTaxNumber($taxCode)->result)->toBeInstanceOf(TaxNumberCheckResult::class);
});

it('lists the document blocks and bank accounts', function () {
    expect(Billingo::documentBlocks()->lazy()->all())->each(fn ($block) => $block->id->toBeInt())
        ->and(Billingo::bankAccounts()->lazy()->all())->each(fn ($account) => $account->id->toBeInt());
});
