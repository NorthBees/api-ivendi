<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Data\Quotes;

use Illuminate\Support\Collection;
use NorthBees\IvendiApi\Data\IvendiError;
use NorthBees\IvendiApi\Enums\FacilityType;
use NorthBees\IvendiApi\Support\Value;

/**
 * The response from POST /v1/quotes. Product groups are flattened across the
 * response's product requests and results.
 */
final readonly class QuoteResult
{
    /**
     * @param  Collection<int, ProductGroup>  $productGroups
     * @param  Collection<int, IvendiError>  $errors
     * @param  Collection<int, IvendiError>  $warnings
     * @param  array<string, mixed>  $attributes  the raw data
     */
    public function __construct(
        public ?string $quotedResultsId,
        public Collection $productGroups,
        public Collection $errors,
        public Collection $warnings,
        public ?string $regulatoryText = null,
        public ?string $commissionDisclosure = null,
        public array $attributes = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $results = collect(Value::list($data, 'quoteResults'))->flatMap(fn (array $quoteResult): array => Value::list($quoteResult, 'results'));

        return new self(
            quotedResultsId: Value::string($data, 'quotedResultsId'),
            productGroups: $results->flatMap(fn (array $result): array => Value::list($result, 'productGroups'))->map(ProductGroup::fromArray(...))->values(),
            errors: IvendiError::collect($data['errors'] ?? null),
            warnings: IvendiError::collect($data['warnings'] ?? null, 'publicWarnings'),
            regulatoryText: Value::string($data, 'quoteeContent.regulatoryText'),
            commissionDisclosure: Value::string($data, 'quoteeContent.commissionDisclosure'),
            attributes: $data,
        );
    }

    /**
     * Every quote without errors, cheapest monthly payment first.
     *
     * @return Collection<int, ProductQuote>
     */
    public function successful(?FacilityType $facilityType = null): Collection
    {
        return $this->productGroups
            ->filter(fn (ProductGroup $group): bool => $facilityType === null || $group->facilityType === $facilityType)
            ->flatMap(fn (ProductGroup $group): Collection => $group->successful())
            ->sortBy(fn (ProductQuote $quote): float => (float) $quote->figures?->regularPayment)
            ->values();
    }

    /**
     * The cheapest quote (by monthly payment) for a facility type.
     */
    public function cheapest(FacilityType $facilityType): ?ProductQuote
    {
        return $this->successful($facilityType)->first();
    }
}
