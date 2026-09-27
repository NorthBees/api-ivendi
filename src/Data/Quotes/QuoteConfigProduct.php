<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Data\Quotes;

use NorthBees\IvendiApi\Enums\FacilityType;
use NorthBees\IvendiApi\Support\Value;

/**
 * A product the retailer offers, with its deposit, term and mileage ranges.
 */
final readonly class QuoteConfigProduct
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        public ?FacilityType $facilityType,
        public ?string $facilityTypeKey,
        public ?string $productName,
        public ?string $productCode,
        public ?Range $deposit,
        public ?Range $term,
        public ?Range $annualMileage,
        public array $attributes = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $key = Value::string($data, 'facilityType');

        return new self(
            facilityType: FacilityType::fromKey($key),
            facilityTypeKey: $key,
            productName: Value::string($data, 'productName'),
            productCode: Value::string($data, 'productCode'),
            deposit: Range::fromArray(Value::object($data, 'deposit')),
            term: Range::fromArray(Value::object($data, 'term')),
            annualMileage: Range::fromArray(Value::object($data, 'annualMileage')),
            attributes: $data,
        );
    }
}
