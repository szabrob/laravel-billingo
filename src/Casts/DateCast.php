<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Casts;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Transformers\Transformer;
use Szabrob\LaravelBillingo\Data\BillingoData;

/**
 * Casts Billingo date strings to CarbonImmutable and formats them back for API payloads.
 */
final class DateCast implements Cast, Transformer
{
    public function __construct(
        private readonly string $format = 'Y-m-d',
    ) {}

    /**
     * @param  array<string, mixed>  $properties
     * @param  CreationContext<BillingoData>  $context
     */
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): ?CarbonImmutable
    {
        if ($value instanceof DateTimeInterface) {
            return CarbonImmutable::instance($value);
        }

        if (! is_string($value) || $value === '') {
            return null;
        }

        return CarbonImmutable::parse($value);
    }

    public function transform(DataProperty $property, mixed $value, TransformationContext $context): mixed
    {
        return $value instanceof DateTimeInterface ? $value->format($this->format) : $value;
    }
}
