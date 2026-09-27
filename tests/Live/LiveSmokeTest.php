<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use NorthBees\IvendiApi\Ivendi;
use NorthBees\IvendiApi\IvendiCredentials;
use NorthBees\IvendiApi\Requests\PaymentSearchRequest;
use NorthBees\IvendiApi\Requests\QuoteConfigRequest;
use NorthBees\IvendiApi\Requests\QuoteRequest;

/*
 * Live contract checks against iVendi Connect. Excluded by default; run with:
 * IVENDI_LIVE=1 IVENDI_BASE_URL=... IVENDI_API_KEY=... IVENDI_QUOTEE_ID=... vendor/bin/pest --group=live
 */

beforeEach(function () {
    if (! env('IVENDI_LIVE')) {
        $this->markTestSkipped('Set IVENDI_LIVE=1 with IVENDI_BASE_URL, IVENDI_API_KEY and IVENDI_QUOTEE_ID to run live checks.');
    }

    Http::allowStrayRequests();

    $this->ivendi = app(Ivendi::class)
        ->withBaseUrl((string) env('IVENDI_BASE_URL'))
        ->withCredentials(new IvendiCredentials((string) env('IVENDI_API_KEY')))
        ->withQuotee((string) env('IVENDI_QUOTEE_ID'));
});

it('fetches quote config', function () {
    expect($this->ivendi->quotes()->config(new QuoteConfigRequest(usedAsset(), 15900))->products)->not->toBeEmpty();
})->group('live');

it('creates quotes', function () {
    expect($this->ivendi->quotes()->create(new QuoteRequest(usedAsset(), 15900, 2000, 48, 10000))->successful())->not->toBeEmpty();
})->group('live');

it('searches payments', function () {
    expect($this->ivendi->paymentSearch()->search(new PaymentSearchRequest(usedAsset(), 15900, [36, 48], [10000], [0, 1000]))->rows)->not->toBeEmpty();
})->group('live');

it('reads the representative example', function () {
    expect($this->ivendi->retailers()->representativeExample()->quote)->not->toBeNull();
})->group('live');
