<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo;

use Szabrob\LaravelBillingo\Resources\BankAccounts;
use Szabrob\LaravelBillingo\Resources\Currencies;
use Szabrob\LaravelBillingo\Resources\DocumentBlocks;
use Szabrob\LaravelBillingo\Resources\DocumentExports;
use Szabrob\LaravelBillingo\Resources\Documents;
use Szabrob\LaravelBillingo\Resources\Inventory;
use Szabrob\LaravelBillingo\Resources\Organization;
use Szabrob\LaravelBillingo\Resources\Partners;
use Szabrob\LaravelBillingo\Resources\Products;
use Szabrob\LaravelBillingo\Resources\Spendings;
use Szabrob\LaravelBillingo\Resources\Utils;

class Billingo
{
    public function __construct(
        protected ?string $apiKey,
        protected string $baseUrl = 'https://api.billingo.hu/v3',
        protected int $timeout = 30,
        protected int $retries = 2,
        protected int $maxRetryDelay = 10,
    ) {}

    /**
     * Get a client instance that uses the given API key instead of the configured one.
     */
    public function withApiKey(string $apiKey): static
    {
        $client = clone $this;
        $client->apiKey = $apiKey;

        return $client;
    }

    public function bankAccounts(): BankAccounts
    {
        return new BankAccounts($this->connector());
    }

    public function currencies(): Currencies
    {
        return new Currencies($this->connector());
    }

    public function documentBlocks(): DocumentBlocks
    {
        return new DocumentBlocks($this->connector());
    }

    public function documentExports(): DocumentExports
    {
        return new DocumentExports($this->connector());
    }

    public function documents(): Documents
    {
        return new Documents($this->connector());
    }

    public function inventory(): Inventory
    {
        return new Inventory($this->connector());
    }

    public function organization(): Organization
    {
        return new Organization($this->connector());
    }

    public function partners(): Partners
    {
        return new Partners($this->connector());
    }

    public function products(): Products
    {
        return new Products($this->connector());
    }

    public function spendings(): Spendings
    {
        return new Spendings($this->connector());
    }

    public function utils(): Utils
    {
        return new Utils($this->connector());
    }

    protected function connector(): Connector
    {
        return new Connector($this->apiKey, $this->baseUrl, $this->timeout, $this->retries, $this->maxRetryDelay);
    }
}
