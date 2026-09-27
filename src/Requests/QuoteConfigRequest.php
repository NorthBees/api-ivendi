<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Requests;

use NorthBees\IvendiApi\Requests\Concerns\HasExtraFields;

/**
 * The deposit, term and mileage ranges of the products a retailer offers for a vehicle.
 */
final readonly class QuoteConfigRequest
{
    use HasExtraFields;

    /**
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public Asset $asset,
        public float $cashPrice,
        public ?string $quoteeId = null,
        public array $extra = [],
    ) {}

    public function withQuotee(string $quoteeId): self
    {
        return new self(...[...get_object_vars($this), 'quoteeId' => $quoteeId]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->payload([
            'quoteeId' => $this->quoteeId,
            'cashPrice' => round($this->cashPrice, 2),
            'asset' => $this->asset->toConfigArray(),
        ]);
    }
}
