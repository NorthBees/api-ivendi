<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use NorthBees\IvendiApi\Exceptions\IvendiAuthenticationException;
use NorthBees\IvendiApi\Exceptions\IvendiConnectionException;
use NorthBees\IvendiApi\Exceptions\IvendiInvalidResponseException;
use NorthBees\IvendiApi\Exceptions\IvendiRequestException;
use NorthBees\IvendiApi\Exceptions\IvendiValidationException;
use NorthBees\IvendiApi\Ivendi;
use NorthBees\IvendiApi\Requests\QuoteConfigRequest;

function fetchQuoteConfig(): void
{
    app(Ivendi::class)->quotes()->config(new QuoteConfigRequest(usedAsset(), 15900));
}

it('maps 400 to a validation exception with the errors', function () {
    Http::fake(['api.ivendi.test/*' => Http::response(['data' => null, 'errors' => ['cashPrice is required', ['message' => 'quoteeId is invalid', 'field' => 'quoteeId']]], 400)]);

    try {
        fetchQuoteConfig();
        $this->fail('Expected an exception.');
    } catch (IvendiValidationException $exception) {
        expect($exception->errors->pluck('message')->all())->toBe(['cashPrice is required', 'quoteeId is invalid'])
            ->and($exception->errors->last()?->field)->toBe('quoteeId')
            ->and($exception->getMessage())->toContain('cashPrice is required')
            ->and($exception->getMessage())->not->toContain('test-api-key');
    }
});

it('maps 401 to an authentication exception', function () {
    Http::fake(['api.ivendi.test/*' => Http::response(['message' => 'Unauthorized'], 401)]);

    fetchQuoteConfig();
})->throws(IvendiAuthenticationException::class);

it('throws when the envelope has errors and no data', function () {
    Http::fake(['api.ivendi.test/*' => Http::response(['data' => null, 'errors' => ['Retailer not found']])]);

    fetchQuoteConfig();
})->throws(IvendiRequestException::class, 'Retailer not found');

it('throws when the envelope has no data', function () {
    Http::fake(['api.ivendi.test/*' => Http::response(['something' => 'else'])]);

    fetchQuoteConfig();
})->throws(IvendiInvalidResponseException::class);

it('retries gateway errors, then succeeds', function () {
    Http::fakeSequence('api.ivendi.test/*')
        ->push('', 504)
        ->push(['data' => ['results' => []], 'errors' => []]);

    fetchQuoteConfig();

    Http::assertSentCount(2);
});

it('throws a connection exception when retries are exhausted', function () {
    Http::fake(['api.ivendi.test/*' => Http::response('', 503)]);

    fetchQuoteConfig();
})->throws(IvendiConnectionException::class);
