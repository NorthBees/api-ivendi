<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Data\Quotes;

use NorthBees\IvendiApi\Support\Value;

/**
 * A parameter's allowed range, with discrete values where the product has them.
 */
final readonly class Range
{
    /**
     * @param  list<int>  $values
     */
    public function __construct(
        public ?float $min,
        public ?float $max,
        public ?float $default,
        public array $values = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): ?self
    {
        if ($data === []) {
            return null;
        }

        return new self(
            min: Value::float($data, 'min'),
            max: Value::float($data, 'max'),
            default: Value::float($data, 'default'),
            values: array_values(array_filter(Value::ints($data, 'values'), fn (int $value): bool => $value > 0)),
        );
    }
}
