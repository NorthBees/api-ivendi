<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Data\Quotes;

use Illuminate\Support\Collection;
use NorthBees\IvendiApi\Data\IvendiError;
use NorthBees\IvendiApi\Enums\FacilityType;
use NorthBees\IvendiApi\Support\Value;

/**
 * One lender product's quote.
 */
final readonly class ProductQuote
{
    /**
     * @param  Collection<int, IvendiError>  $errors  public errors
     * @param  Collection<int, IvendiError>  $warnings  public warnings
     * @param  array<string, mixed>  $attributes  the raw product quote
     */
    public function __construct(
        public ?string $quoteId,
        public ?string $productId,
        public ?string $productName,
        public ?string $productCode,
        public ?string $quoteeProductCode,
        public ?string $funderCode,
        public ?string $funderName,
        public ?FacilityType $facilityType,
        public ?QuoteFigures $figures,
        public Collection $errors,
        public Collection $warnings,
        public array $attributes = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data, ?FacilityType $facilityType = null): self
    {
        $figures = Value::object($data, 'figures');

        return new self(
            quoteId: Value::string($data, 'quoteId'),
            productId: Value::string($data, 'productId'),
            productName: Value::string($data, 'productName') ?? Value::string($data, 'name'),
            productCode: Value::string($data, 'productCode'),
            quoteeProductCode: Value::string($data, 'quoteeProductCode'),
            funderCode: Value::string($data, 'funderCode'),
            funderName: Value::string($data, 'funderName') ?? Value::string($data, 'funder.name'),
            facilityType: FacilityType::fromKey(Value::string($data, 'facilityType')) ?? $facilityType ?? FacilityType::fromKey(Value::string($data, 'productCode')),
            figures: $figures === [] ? null : QuoteFigures::fromArray($figures),
            errors: IvendiError::collect($data['errors'] ?? null),
            warnings: IvendiError::collect($data['warnings'] ?? null, 'publicWarnings'),
            attributes: $data,
        );
    }

    public function hasErrors(): bool
    {
        return $this->errors->isNotEmpty() || Value::bool($this->attributes, 'hasErrors') || $this->figures?->regularPayment === null;
    }
}
