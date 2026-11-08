<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data;

use Spatie\LaravelData\Data;

abstract class BillingoData extends Data
{
    /**
     * Transform the data object into a Billingo API payload, omitting unset (null) values.
     *
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return self::withoutNulls($this->toArray());
    }

    /**
     * @param  array<array-key, mixed>  $values
     * @return array<array-key, mixed>
     */
    private static function withoutNulls(array $values): array
    {
        $result = [];

        foreach ($values as $key => $value) {
            if ($value === null) {
                continue;
            }

            $result[$key] = is_array($value) ? self::withoutNulls($value) : $value;
        }

        return $result;
    }
}
