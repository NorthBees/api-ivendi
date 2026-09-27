<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Data\PaymentSearch;

use Illuminate\Support\Collection;
use NorthBees\IvendiApi\Support\Value;

/**
 * The response from POST /v1/payment-search.
 */
final readonly class PaymentSearchResult
{
    /**
     * @param  Collection<int, PaymentSearchRow>  $rows
     * @param  array<string, mixed>  $attributes  the raw data
     */
    public function __construct(
        public Collection $rows,
        public array $attributes = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            rows: collect(Value::list($data, 'financeProductResults'))->map(PaymentSearchRow::fromArray(...))->values(),
            attributes: $data,
        );
    }
}
