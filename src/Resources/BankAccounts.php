<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Resources;

use Illuminate\Support\LazyCollection;
use Szabrob\LaravelBillingo\Data\BankAccounts\BankAccountData;
use Szabrob\LaravelBillingo\Support\PaginatedResponse;

final class BankAccounts extends Resource
{
    /**
     * @return PaginatedResponse<BankAccountData>
     */
    public function list(int $page = 1, int $perPage = 25): PaginatedResponse
    {
        $response = $this->connector->get('bank-accounts', ['page' => $page, 'per_page' => $perPage]);

        return PaginatedResponse::fromArray($this->json($response), BankAccountData::class);
    }

    /**
     * @return LazyCollection<int, BankAccountData>
     */
    public function lazy(int $perPage = 100): LazyCollection
    {
        return $this->lazily(fn (int $page): PaginatedResponse => $this->list($page, $perPage));
    }

    public function get(int $id): BankAccountData
    {
        return BankAccountData::from($this->json($this->connector->get("bank-accounts/{$id}")));
    }

    public function create(BankAccountData $bankAccount): BankAccountData
    {
        return BankAccountData::from($this->json($this->connector->post('bank-accounts', $bankAccount->toPayload())));
    }

    public function update(int $id, BankAccountData $bankAccount): BankAccountData
    {
        return BankAccountData::from($this->json($this->connector->put("bank-accounts/{$id}", $bankAccount->toPayload())));
    }

    public function delete(int $id): void
    {
        $this->connector->delete("bank-accounts/{$id}");
    }
}
