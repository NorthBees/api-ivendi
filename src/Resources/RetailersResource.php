<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Resources;

use NorthBees\IvendiApi\Data\Quotes\RepresentativeExample;
use NorthBees\IvendiApi\Enums\Endpoint;

final class RetailersResource extends Resource
{
    /**
     * The representative example the retailer has configured, defaulting to the client's quotee.
     */
    public function representativeExample(?string $retailerId = null): RepresentativeExample
    {
        return RepresentativeExample::fromArray($this->send(Endpoint::RepresentativeExample, null, $this->quoteeId($retailerId)));
    }
}
