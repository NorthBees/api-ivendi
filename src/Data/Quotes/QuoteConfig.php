<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Data\Quotes;

use Illuminate\Support\Collection;
use NorthBees\IvendiApi\Data\IvendiError;
use NorthBees\IvendiApi\Support\Value;

/**
 * The response from POST /v1/quote-config.
 */
final readonly class QuoteConfig
{
    /**
     * @param  Collection<int, QuoteConfigProduct>  $products
     * @param  Collection<int, IvendiError>  $errors
     */
    public function __construct(
        public ?string $responseId,
        public Collection $products,
        public Collection $errors,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            responseId: Value::string($data, 'responseId'),
            products: collect(Value::list($data, 'results'))->map(QuoteConfigProduct::fromArray(...))->values(),
            errors: IvendiError::collect($data['errors'] ?? null),
        );
    }
}
