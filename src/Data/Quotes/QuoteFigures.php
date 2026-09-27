<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Data\Quotes;

use NorthBees\IvendiApi\Support\Value;

/**
 * The figures of a product quote. Amounts are in pounds.
 */
final readonly class QuoteFigures
{
    /**
     * @param  array<string, mixed>  $attributes  the raw figures
     */
    public function __construct(
        public ?float $regularPayment,
        public ?float $firstPayment,
        public ?float $finalPayment,
        public ?float $balloon,
        public ?int $term,
        public ?float $termQuoted,
        public ?int $numberOfRegularPayments,
        public ?float $totalCashPrice,
        public ?float $cashDeposit,
        public ?float $totalDeposit,
        public ?float $advance,
        public ?float $totalPayable,
        public ?float $totalCharges,
        public ?float $apr,
        public ?float $interestRate,
        public ?float $flatRate,
        public ?float $acceptanceFee,
        public ?float $optionToPurchaseFee,
        public ?int $annualDistanceQuoted,
        public ?float $chargePerOverDistanceUnit,
        public bool $adjustedTerm,
        public bool $adjustedDistance,
        public array $attributes = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            regularPayment: Value::float($data, 'regularPayment') ?? Value::float($data, 'paymentSchedules.0.amount'),
            firstPayment: Value::float($data, 'firstPayment'),
            finalPayment: Value::float($data, 'finalPayment'),
            balloon: Value::float($data, 'balloon'),
            term: Value::int($data, 'term'),
            termQuoted: Value::float($data, 'termQuoted'),
            numberOfRegularPayments: Value::int($data, 'numberOfRegularPayments'),
            totalCashPrice: Value::float($data, 'totalCashPrice'),
            cashDeposit: Value::float($data, 'asset.cashDeposit'),
            totalDeposit: Value::float($data, 'totalDeposit'),
            advance: Value::float($data, 'advance'),
            totalPayable: Value::float($data, 'totalPayable'),
            totalCharges: Value::float($data, 'totalCharges') ?? Value::float($data, 'interestCharges'),
            apr: Value::float($data, 'apr'),
            interestRate: Value::float($data, 'interestRate'),
            flatRate: Value::float($data, 'flatRate'),
            acceptanceFee: Value::float($data, 'acceptanceFee'),
            optionToPurchaseFee: Value::float($data, 'optionToPurchaseFee'),
            annualDistanceQuoted: Value::int($data, 'asset.annualDistanceQuoted'),
            chargePerOverDistanceUnit: Value::float($data, 'asset.chargePerOverDistanceUnit'),
            adjustedTerm: Value::bool($data, 'asset.adjustedTerm'),
            adjustedDistance: Value::bool($data, 'asset.adjustedDistance'),
            attributes: $data,
        );
    }

    /**
     * The balloon / optional final payment for residual based products, otherwise null.
     */
    public function optionalFinalPayment(): ?float
    {
        return $this->balloon !== null && $this->balloon > 0 ? $this->balloon : null;
    }
}
