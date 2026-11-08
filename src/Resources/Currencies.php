<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Resources;

use DateTimeInterface;
use Szabrob\LaravelBillingo\Data\Currencies\ConversionRateData;
use Szabrob\LaravelBillingo\Enums\Currency;

final class Currencies extends Resource
{
    public function conversionRate(Currency $from, Currency $to, ?DateTimeInterface $date = null): ConversionRateData
    {
        $response = $this->connector->get('currencies', array_filter([
            'from' => $from->value,
            'to' => $to->value,
            'date' => $date?->format('Y-m-d'),
        ]));

        return ConversionRateData::from($this->json($response));
    }
}
