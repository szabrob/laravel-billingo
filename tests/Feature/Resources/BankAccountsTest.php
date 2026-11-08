<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Szabrob\LaravelBillingo\Data\BankAccounts\BankAccountData;
use Szabrob\LaravelBillingo\Enums\Currency;
use Szabrob\LaravelBillingo\Facades\Billingo;

beforeEach(fn () => Http::preventStrayRequests());

$accountResponse = ['id' => 3, 'name' => 'OTP', 'account_number' => '11773016-11111111', 'currency' => 'HUF', 'need_qr' => true];

it('manages bank accounts', function () use ($accountResponse) {
    Http::fake([
        'api.billingo.hu/v3/bank-accounts?*' => Http::response(['data' => [$accountResponse], 'total' => 1, 'per_page' => 25, 'current_page' => 1, 'last_page' => 1]),
        'api.billingo.hu/v3/bank-accounts' => Http::response($accountResponse, 201),
        'api.billingo.hu/v3/bank-accounts/3' => Http::sequence()->push($accountResponse)->push($accountResponse)->push(null, 204),
    ]);
    $account = new BankAccountData(name: 'OTP', accountNumber: '11773016-11111111', currency: Currency::HUF);

    expect(Billingo::bankAccounts()->list()->data->first()?->needQr)->toBeTrue()
        ->and(Billingo::bankAccounts()->lazy()->count())->toBe(1)
        ->and(Billingo::bankAccounts()->create($account)->id)->toBe(3)
        ->and(Billingo::bankAccounts()->get(3)->currency)->toBe(Currency::HUF)
        ->and(Billingo::bankAccounts()->update(3, $account)->name)->toBe('OTP');
    Billingo::bankAccounts()->delete(3);

    assertBillingoRequest('POST', 'bank-accounts', ['name' => 'OTP', 'account_number' => '11773016-11111111', 'currency' => 'HUF']);
    assertBillingoRequest('PUT', 'bank-accounts/3');
    assertBillingoRequest('DELETE', 'bank-accounts/3');
});
