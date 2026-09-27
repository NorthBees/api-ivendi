<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Enums;

/**
 * The iVendi Connect endpoints this SDK calls. The value is the key used by Ivendi::fake().
 */
enum Endpoint: string
{
    case QuoteConfig = 'quotes.config';
    case CreateQuotes = 'quotes.create';
    case FindQuote = 'quotes.find';
    case PaymentSearch = 'paymentSearch.search';
    case RepresentativeExample = 'retailers.representativeExample';

    public function method(): string
    {
        return in_array($this, [self::FindQuote, self::RepresentativeExample], true) ? 'GET' : 'POST';
    }

    public function path(string ...$parameters): string
    {
        return match ($this) {
            self::QuoteConfig => '/v1/quote-config',
            self::CreateQuotes => '/v1/quotes',
            self::FindQuote => '/v1/quotes/'.rawurlencode($parameters[0] ?? ''),
            self::PaymentSearch => '/v1/payment-search',
            self::RepresentativeExample => '/v1/retailers/'.rawurlencode($parameters[0] ?? '').'/representative-example',
        };
    }

    public static function fromRequest(string $method, string $path): ?self
    {
        $path = '/'.strtolower(trim($path, '/'));
        $isGet = strtoupper($method) === 'GET';

        return match (true) {
            $path === '/v1/quote-config' => self::QuoteConfig,
            $path === '/v1/quotes' && ! $isGet => self::CreateQuotes,
            $path === '/v1/payment-search' => self::PaymentSearch,
            (bool) preg_match('#^/v1/retailers/[^/]+/representative-example$#', $path) => self::RepresentativeExample,
            (bool) preg_match('#^/v1/quotes/[^/]+$#', $path) => self::FindQuote,
            default => null,
        };
    }
}
