<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Resources;

use Szabrob\LaravelBillingo\Data\Utils\IdConversionData;
use Szabrob\LaravelBillingo\Data\Utils\ServerTimeData;
use Szabrob\LaravelBillingo\Data\Utils\TaxNumberCheckData;

final class Utils extends Resource
{
    /**
     * Validate a Hungarian tax number (format: 12345678-1-12) against NAV.
     */
    public function checkTaxNumber(string $taxNumber): TaxNumberCheckData
    {
        return TaxNumberCheckData::from($this->json($this->connector->get('utils/check-tax-number/'.rawurlencode($taxNumber))));
    }

    /**
     * Convert a Billingo API v2 (legacy) ID to its API v3 counterpart.
     */
    public function convertLegacyId(int $legacyId): IdConversionData
    {
        return IdConversionData::from($this->json($this->connector->get("utils/convert-legacy-id/{$legacyId}")));
    }

    public function serverTime(): ServerTimeData
    {
        return ServerTimeData::from($this->json($this->connector->get('utils/time')));
    }
}
