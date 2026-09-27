<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Data\PaymentSearch;

use NorthBees\IvendiApi\Enums\FacilityType;
use NorthBees\IvendiApi\Support\Value;

/**
 * One product's monthly payment for a combination of term, deposit and mileage.
 */
final readonly class PaymentSearchProduct
{
    public function __construct(
        public ?string $key,
        public ?float $payment,
        public ?string $displayName,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            key: Value::string($data, 'key'),
            payment: Value::float($data, 'payment'),
            displayName: Value::string($data, 'displayName'),
        );
    }

    public function facilityType(): ?FacilityType
    {
        return FacilityType::fromKey($this->key);
    }

    public function isQuoted(): bool
    {
        return $this->payment !== null && $this->payment > 0;
    }
}
