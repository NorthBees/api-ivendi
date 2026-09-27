<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Resources;

use NorthBees\IvendiApi\Enums\Endpoint;
use NorthBees\IvendiApi\Exceptions\IvendiMissingConfigurationException;
use NorthBees\IvendiApi\Http\HttpTransport;
use NorthBees\IvendiApi\Ivendi;

/**
 * A group of iVendi Connect endpoints.
 */
abstract class Resource
{
    public function __construct(
        protected readonly Ivendi $ivendi,
        protected readonly HttpTransport $transport,
    ) {}

    /**
     * @param  array<array-key, mixed>|null  $payload
     * @return array<string, mixed>
     */
    protected function send(Endpoint $endpoint, ?array $payload = null, string ...$pathParameters): array
    {
        return $this->transport->send(
            $endpoint,
            $this->ivendi->baseUrl(),
            $this->ivendi->credentials()->apiKey,
            $payload,
            ...$pathParameters,
        );
    }

    /**
     * The request's quotee, falling back to the client's.
     *
     * @throws IvendiMissingConfigurationException
     */
    protected function quoteeId(?string $quoteeId): string
    {
        return $quoteeId
            ?? $this->ivendi->quoteeId()
            ?? throw new IvendiMissingConfigurationException('An iVendi quoteeId has not been configured or supplied.');
    }
}
