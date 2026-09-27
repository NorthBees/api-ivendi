<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use NorthBees\IvendiApi\Enums\Endpoint;
use NorthBees\IvendiApi\Enums\FacilityType;
use NorthBees\IvendiApi\Exceptions\IvendiMissingConfigurationException;
use NorthBees\IvendiApi\Facades\Ivendi as IvendiFacade;
use NorthBees\IvendiApi\Ivendi;
use NorthBees\IvendiApi\Requests\QuoteConfigRequest;
use NorthBees\IvendiApi\Requests\QuoteRequest;
use NorthBees\IvendiApi\Testing\IvendiResponse;

it('fetches quote config for the client quotee', function () {
    $fake = IvendiFacade::fake([Endpoint::QuoteConfig->value => IvendiResponse::quoteConfig()]);

    $config = app(Ivendi::class)->withQuotee('TENANT-QUOTEE')->quotes()->config(new QuoteConfigRequest(usedAsset(), 15900));

    expect($config->products)->toHaveCount(2)
        ->and($config->products[0]->facilityType)->toBe(FacilityType::HirePurchase)
        ->and($config->products[1]->term?->values)->toBe([24, 36, 48, 60])
        ->and($config->products[1]->deposit?->max)->toBe(10000.0)
        ->and($config->products[1]->annualMileage?->values)->toBe([])
        ->and($config->products[1]->annualMileage?->default)->toBe(10000.0);

    $fake->assertSent(Endpoint::QuoteConfig, fn (array $payload): bool => $payload['quoteeId'] === 'TENANT-QUOTEE');
});

it('creates quotes and picks the cheapest per facility type', function () {
    $fake = IvendiFacade::fake([Endpoint::CreateQuotes->value => IvendiResponse::quotes([
        IvendiResponse::productGroup('HP', [
            IvendiResponse::productQuote('HP', ['regularPayment' => 360.0], ['quoteId' => 'expensive']),
            IvendiResponse::productQuote('HP', ['regularPayment' => 340.0], ['quoteId' => 'cheap']),
            IvendiResponse::productQuote('HP', ['regularPayment' => 300.0], ['quoteId' => 'errored', 'errors' => ['hasPublicErrors' => true, 'publicErrors' => [['message' => 'Declined', 'number' => 12]]]]),
        ]),
        IvendiResponse::productGroup('PCP'),
    ], regulatoryText: 'We are a credit broker')]);

    $result = app(Ivendi::class)->quotes()->create(new QuoteRequest(usedAsset(), 15000, 1000, 48, 10000));

    $hp = $result->cheapest(FacilityType::HirePurchase);
    $pcp = $result->cheapest(FacilityType::PersonalContractPurchase);

    expect($hp?->quoteId)->toBe('cheap')
        ->and($hp?->figures?->regularPayment)->toBe(340.0)
        ->and($hp?->figures?->apr)->toBe(9.9)
        ->and($hp?->figures?->totalPayable)->toBe(17799.52)
        ->and($hp?->figures?->cashDeposit)->toBe(1000.0)
        ->and($hp?->figures?->optionToPurchaseFee)->toBe(10.0)
        ->and($hp?->figures?->optionalFinalPayment())->toBeNull()
        ->and($pcp?->facilityType)->toBe(FacilityType::PersonalContractPurchase)
        ->and($pcp?->figures?->optionalFinalPayment())->toBe(6000.0)
        ->and($pcp?->figures?->chargePerOverDistanceUnit)->toBe(8.0)
        ->and($result->successful())->toHaveCount(3)
        ->and($result->productGroups[0]->quotes[2]->errors->first()?->message)->toBe('Declined')
        ->and($result->productGroups[0]->quotes[2]->errors->first()?->code)->toBe('12')
        ->and($result->regulatoryText)->toBe('We are a credit broker');

    $fake->assertSent(Endpoint::CreateQuotes, fn (array $payload): bool => $payload['quoteeId'] === 'CONFIG-QUOTEE' && $payload['term'] === 48);

    Http::assertSent(fn (Request $request): bool => $request->hasHeader('x-api-key', 'test-api-key')
        && $request->hasHeader('Accept-Language', 'en-GB')
        && $request->url() === 'https://api.ivendi.test/v1/quotes');
});

it('finds a quote by ID', function () {
    IvendiFacade::fake([Endpoint::FindQuote->value => IvendiResponse::productQuote('PCP')]);

    expect(app(Ivendi::class)->quotes()->find('quote-pcp')?->figures?->regularPayment)->toBe(249.5);

    Http::assertSent(fn (Request $request): bool => $request->method() === 'GET' && str_ends_with($request->url(), '/v1/quotes/quote-pcp'));
});

it('requires a quotee', function () {
    config()->set('ivendi.quotee_id', null);
    app()->forgetScopedInstances();
    $fake = IvendiFacade::fake();

    expect(fn () => app(Ivendi::class)->quotes()->create(new QuoteRequest(usedAsset(), 15000, 1000, 48, 10000)))
        ->toThrow(IvendiMissingConfigurationException::class);

    $fake->assertNothingSent();
});
