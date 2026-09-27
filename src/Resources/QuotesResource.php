<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Resources;

use NorthBees\IvendiApi\Data\Quotes\ProductQuote;
use NorthBees\IvendiApi\Data\Quotes\QuoteConfig;
use NorthBees\IvendiApi\Data\Quotes\QuoteResult;
use NorthBees\IvendiApi\Enums\Endpoint;
use NorthBees\IvendiApi\Requests\QuoteConfigRequest;
use NorthBees\IvendiApi\Requests\QuoteRequest;
use NorthBees\IvendiApi\Support\Value;

final class QuotesResource extends Resource
{
    /**
     * The deposit, term and mileage ranges of the retailer's products for a vehicle.
     */
    public function config(QuoteConfigRequest $request): QuoteConfig
    {
        $request = $request->withQuotee($this->quoteeId($request->quoteeId));

        return QuoteConfig::fromArray($this->send(Endpoint::QuoteConfig, $request->toArray()));
    }

    /**
     * Full quotes for every product the retailer offers.
     */
    public function create(QuoteRequest $request): QuoteResult
    {
        $request = $request->withQuotee($this->quoteeId($request->quoteeId));

        return QuoteResult::fromArray($this->send(Endpoint::CreateQuotes, $request->toArray()));
    }

    /**
     * A previously returned quote.
     */
    public function find(string $quoteId): ?ProductQuote
    {
        $data = $this->send(Endpoint::FindQuote, null, $quoteId);
        $quote = Value::object($data, 'productQuote') ?: $data;

        return $quote === [] ? null : ProductQuote::fromArray($quote);
    }
}
