<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Data\Quotes;

use NorthBees\IvendiApi\Support\Value;

/**
 * The representative example a retailer has configured in iVendi TRANSACT.
 */
final readonly class RepresentativeExample
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        public ?ProductQuote $quote,
        public ?string $funderCode,
        public ?string $funderName,
        public ?bool $isNew,
        public ?string $registrationDate,
        public ?int $termDistance,
        public array $attributes = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $quote = Value::object($data, 'productQuote');

        return new self(
            quote: $quote === [] ? null : ProductQuote::fromArray([...$quote, 'funderName' => Value::string($data, 'funder.name')]),
            funderCode: Value::string($data, 'funder.code'),
            funderName: Value::string($data, 'funder.name'),
            isNew: data_get($data, 'asset.isNew') === null ? null : Value::bool($data, 'asset.isNew'),
            registrationDate: Value::string($data, 'asset.registrationDate'),
            termDistance: Value::int($data, 'asset.termDistance'),
            attributes: $data,
        );
    }
}
