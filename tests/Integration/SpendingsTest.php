<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Szabrob\LaravelBillingo\Data\Spendings\SaveSpendingData;
use Szabrob\LaravelBillingo\Enums\Currency;
use Szabrob\LaravelBillingo\Enums\PaymentMethod;
use Szabrob\LaravelBillingo\Enums\SpendingCategory;
use Szabrob\LaravelBillingo\Exceptions\BillingoException;
use Szabrob\LaravelBillingo\Facades\Billingo;

beforeEach(fn () => sandbox());

function sandboxSpending(string $comment): SaveSpendingData
{
    return new SaveSpendingData(
        currency: Currency::HUF,
        totalGross: 1270,
        totalGrossHuf: 1270,
        totalVatAmount: 270,
        totalVatAmountHuf: 270,
        fulfillmentDate: CarbonImmutable::today(),
        category: SpendingCategory::Other,
        paymentMethod: PaymentMethod::Cash,
        comment: $comment,
    );
}

it('creates, reads, updates and deletes a spending', function () {
    $comment = sandboxName('spending');

    $spending = Billingo::spendings()->create(sandboxSpending($comment));
    $id = (int) $spending->id;

    try {
        $fetched = Billingo::spendings()->get($id);

        expect($fetched->comment)->toBe($comment)
            ->and($fetched->category)->toBe(SpendingCategory::Other)
            ->and($fetched->currency)->toBe(Currency::HUF)
            ->and($fetched->totalGross)->toEqual(1270)
            ->and($fetched->fulfillmentDate?->toDateString())->toBe(CarbonImmutable::today()->toDateString());

        $updated = Billingo::spendings()->update($id, sandboxSpending($comment.' updated'));

        expect($updated->comment)->toBe($comment.' updated');
    } finally {
        Billingo::spendings()->delete($id);
    }

    expect(fn () => Billingo::spendings()->get($id))->toThrow(BillingoException::class);
});
