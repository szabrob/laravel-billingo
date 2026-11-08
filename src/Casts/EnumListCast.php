<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Casts;

use BackedEnum;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Transformers\Transformer;
use Szabrob\LaravelBillingo\Data\BillingoData;

/**
 * Casts a list of raw values to backed enum cases and back, independent of the
 * host application's laravel-data iterable casting setting.
 */
final class EnumListCast implements Cast, Transformer
{
    /**
     * @param  class-string<BackedEnum>  $enum
     */
    public function __construct(
        private readonly string $enum,
    ) {}

    /**
     * @param  array<string, mixed>  $properties
     * @param  CreationContext<BillingoData>  $context
     * @return list<BackedEnum>|null
     */
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): ?array
    {
        if (! is_array($value)) {
            return null;
        }

        return array_values(array_map(
            fn (mixed $item): BackedEnum => $item instanceof BackedEnum ? $item : ($this->enum)::from(is_int($item) ? $item : (string) $item),
            $value,
        ));
    }

    public function transform(DataProperty $property, mixed $value, TransformationContext $context): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        return array_map(fn (mixed $item): mixed => $item instanceof BackedEnum ? $item->value : $item, $value);
    }
}
