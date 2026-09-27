<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Data\PaymentSearch;

use Illuminate\Support\Collection;
use NorthBees\IvendiApi\Support\Value;

/**
 * The product payments for one combination of term, deposit and mileage.
 */
final readonly class PaymentSearchRow
{
    /**
     * @param  Collection<int, PaymentSearchProduct>  $products
     */
    public function __construct(
        public ?int $term,
        public ?int $annualMileage,
        public ?float $deposit,
        public ?string $creditTier,
        public Collection $products,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            term: Value::int($data, 'term'),
            annualMileage: Value::int($data, 'annualMileage'),
            deposit: Value::float($data, 'deposits') ?? Value::float($data, 'deposit'),
            creditTier: Value::string($data, 'creditTier'),
            products: collect(Value::list($data, 'productResults'))->map(PaymentSearchProduct::fromArray(...))->values(),
        );
    }
}
