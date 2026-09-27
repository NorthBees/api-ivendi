<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Resources;

use NorthBees\IvendiApi\Data\PaymentSearch\PaymentSearchResult;
use NorthBees\IvendiApi\Enums\Endpoint;
use NorthBees\IvendiApi\Requests\PaymentSearchRequest;

final class PaymentSearchResource extends Resource
{
    /**
     * Monthly payments for one vehicle across a matrix of terms, deposits and mileages.
     */
    public function search(PaymentSearchRequest $request): PaymentSearchResult
    {
        $request = $request->withQuotee($this->quoteeId($request->quoteeId));

        return PaymentSearchResult::fromArray($this->send(Endpoint::PaymentSearch, $request->toArray()));
    }
}
