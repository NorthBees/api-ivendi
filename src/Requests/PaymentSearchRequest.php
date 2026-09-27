<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Requests;

use NorthBees\IvendiApi\Enums\CreditTier;
use NorthBees\IvendiApi\Requests\Concerns\HasExtraFields;

/**
 * Monthly payments (without full quote information) for one vehicle across a matrix
 * of terms, deposits and annual mileages.
 */
final readonly class PaymentSearchRequest
{
    use HasExtraFields;

    /**
     * @param  list<int>  $terms  in months
     * @param  list<int>  $annualMileages
     * @param  list<int|float>  $deposits  cash amounts
     * @param  list<CreditTier>  $creditTiers
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public Asset $asset,
        public float $cashPrice,
        public array $terms,
        public array $annualMileages,
        public array $deposits,
        public array $creditTiers = [],
        public ?string $quoteeId = null,
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
        $payload = $this->payload([
            'mode' => $this->mode,
            'quoteeId' => $this->quoteeId,
            'currency' => $this->currency,
            'cashPrice' => round($this->cashPrice, 2),
            'parameters' => [
                'termsInMonths' => $this->terms,
                'annualMileages' => $this->annualMileages,
                'deposits' => $this->deposits,
            ],
            'asset' => $this->asset->toArray(),
        ]);

        // creditTiers is required; iVendi's documented request sends [""] when not using tiers.
        $payload['parameters']['creditTiers'] = $this->creditTiers === []
            ? ['']
            : array_map(fn (CreditTier $tier): string => $tier->value, $this->creditTiers);

        return $payload;
    }
}
