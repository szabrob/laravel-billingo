<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Documents;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\WithCastAndTransformer;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Casts\DateCast;
use Szabrob\LaravelBillingo\Data\BillingoData;
use Szabrob\LaravelBillingo\Enums\PaymentMethod;

#[MapName(SnakeCaseMapper::class)]
class CreateModificationDocumentData extends BillingoData
{
    /**
     * @param  array<int, ProductItemData|CustomItemData>|null  $items
     */
    public function __construct(
        #[WithCastAndTransformer(DateCast::class)]
        public ?CarbonImmutable $dueDate = null,
        public ?string $comment = null,
        public ?PaymentMethod $paymentMethod = null,
        public ?bool $withoutFinancialFulfillment = null,
        public ?array $items = null,
    ) {}
}
