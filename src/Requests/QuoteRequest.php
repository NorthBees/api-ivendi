<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Requests;

use NorthBees\IvendiApi\Requests\Concerns\HasExtraFields;

/**
 * Full quotes for one vehicle, for every product the retailer offers.
 */
final readonly class QuoteRequest
{
    use HasExtraFields;

    /**
     * @param  string|null  $quoteeId  the retailer; defaults to the client's quotee
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public Asset $asset,
        public float $cashPrice,
        public float $cashDeposit,
        public int $term,
        public int $annualDistance,
        public ?string $quoteeId = null,
        public ?int $customerType = null,
        public float $cashBack = 0.0,
        public int $mode = 0,
        public string $currency = 'GBP',
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
            'mode' => $this->mode,
            'quoteeId' => $this->quoteeId,
            'currency' => $this->currency,
            'cashPrice' => round($this->cashPrice, 2),
            'cashDeposit' => round($this->cashDeposit, 2),
            'cashBack' => round($this->cashBack, 2),
            'term' => $this->term,
            'annualDistance' => $this->annualDistance,
            'annualDistanceUnits' => $this->asset->odometerUnits->value,
            'customerType' => $this->customerType,
            'asset' => $this->asset->toArray(),
        ]);
    }
}
