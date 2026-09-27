<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Facades;

use Illuminate\Support\Facades\Facade;
use NorthBees\IvendiApi\Testing\IvendiFake;

/**
 * @method static \NorthBees\IvendiApi\Ivendi withCredentials(\NorthBees\IvendiApi\IvendiCredentials $credentials)
 * @method static \NorthBees\IvendiApi\Ivendi withBaseUrl(string $baseUrl)
 * @method static \NorthBees\IvendiApi\Ivendi withQuotee(string $quoteeId)
 * @method static \NorthBees\IvendiApi\IvendiCredentials credentials()
 * @method static bool hasCredentials()
 * @method static string baseUrl()
 * @method static string|null quoteeId()
 * @method static \NorthBees\IvendiApi\Resources\QuotesResource quotes()
 * @method static \NorthBees\IvendiApi\Resources\PaymentSearchResource paymentSearch()
 * @method static \NorthBees\IvendiApi\Resources\RetailersResource retailers()
 *
 * @see \NorthBees\IvendiApi\Ivendi
 */
class Ivendi extends Facade
{
    /**
     * Fake iVendi responses. Keys are endpoint names (e.g. "quotes.create",
     * "paymentSearch.search") or "*"; values are the envelope's `data`, Http::response()
     * results, or closures receiving the request payload and returning either.
     *
     * @param  array<string, mixed>  $responses
     */
    public static function fake(array $responses = []): IvendiFake
    {
        return IvendiFake::fake($responses);
    }

    protected static function getFacadeAccessor(): string
    {
        return \NorthBees\IvendiApi\Ivendi::class;
    }
}
