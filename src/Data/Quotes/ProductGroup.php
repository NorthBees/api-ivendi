<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Data\Quotes;

use Illuminate\Support\Collection;
use NorthBees\IvendiApi\Data\IvendiError;
use NorthBees\IvendiApi\Enums\FacilityType;
use NorthBees\IvendiApi\Support\Value;

/**
 * The quotes for one facility type (HP, PCP, ...), one per lender product.
 */
final readonly class ProductGroup
{
    /**
     * @param  Collection<int, ProductQuote>  $quotes
     * @param  Collection<int, IvendiError>  $errors
     */
    public function __construct(
        public ?string $facilityTypeKey,
        public ?FacilityType $facilityType,
        public Collection $quotes,
        public Collection $errors,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $key = Value::string($data, 'facilityType');
        $facilityType = FacilityType::fromKey($key);

        return new self(
            facilityTypeKey: $key,
            facilityType: $facilityType,
            quotes: collect(Value::list($data, 'productQuotes'))->map(fn (array $quote): ProductQuote => ProductQuote::fromArray($quote, $facilityType))->values(),
            errors: IvendiError::collect($data['errors'] ?? null),
        );
    }

    /**
     * Quotes without errors, cheapest monthly payment first.
     *
     * @return Collection<int, ProductQuote>
     */
    public function successful(): Collection
    {
        return $this->quotes
            ->reject(fn (ProductQuote $quote): bool => $quote->hasErrors())
            ->sortBy(fn (ProductQuote $quote): float => (float) $quote->figures?->regularPayment)
            ->values();
    }
}
