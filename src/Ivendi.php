<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi;

use NorthBees\IvendiApi\Exceptions\IvendiMissingConfigurationException;
use NorthBees\IvendiApi\Http\HttpTransport;
use NorthBees\IvendiApi\Resources\PaymentSearchResource;
use NorthBees\IvendiApi\Resources\QuotesResource;
use NorthBees\IvendiApi\Resources\RetailersResource;

/**
 * Entry point for the iVendi Connect API. Immutable: the with*() methods return new
 * instances, so one tenant's retailer or key never leaks into another's.
 */
final readonly class Ivendi
{
    /**
     * @param  array<string, mixed>  $config  the resolved `ivendi` config
     */
    public function __construct(
        private HttpTransport $transport,
        private array $config,
        private ?IvendiCredentials $credentials = null,
        private ?string $baseUrl = null,
        private ?string $quoteeId = null,
    ) {}

    public function withCredentials(IvendiCredentials $credentials): self
    {
        return new self($this->transport, $this->config, $credentials, $this->baseUrl, $this->quoteeId);
    }

    public function withBaseUrl(string $baseUrl): self
    {
        return new self($this->transport, $this->config, $this->credentials, $baseUrl, $this->quoteeId);
    }

    /**
     * The retailer quoted for, when a request does not name its own quoteeId.
     */
    public function withQuotee(string $quoteeId): self
    {
        return new self($this->transport, $this->config, $this->credentials, $this->baseUrl, $quoteeId);
    }

    /**
     * Instance credentials, falling back to config.
     *
     * @throws IvendiMissingConfigurationException
     */
    public function credentials(): IvendiCredentials
    {
        return $this->credentials
            ?? IvendiCredentials::fromConfig($this->config)
            ?? throw new IvendiMissingConfigurationException('An iVendi API key has not been configured.');
    }

    public function hasCredentials(): bool
    {
        return ($this->credentials ?? IvendiCredentials::fromConfig($this->config)) !== null;
    }

    /**
     * @throws IvendiMissingConfigurationException
     */
    public function baseUrl(): string
    {
        $baseUrl = $this->baseUrl ?? $this->config['base_url'] ?? null;

        return is_string($baseUrl) && $baseUrl !== ''
            ? $baseUrl
            : throw new IvendiMissingConfigurationException('The iVendi base URL has not been configured.');
    }

    public function quoteeId(): ?string
    {
        $quoteeId = $this->quoteeId ?? $this->config['quotee_id'] ?? null;

        return is_string($quoteeId) && $quoteeId !== '' ? $quoteeId : null;
    }

    public function quotes(): QuotesResource
    {
        return new QuotesResource($this, $this->transport);
    }

    public function paymentSearch(): PaymentSearchResource
    {
        return new PaymentSearchResource($this, $this->transport);
    }

    public function retailers(): RetailersResource
    {
        return new RetailersResource($this, $this->transport);
    }
}
