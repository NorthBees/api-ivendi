<?php

declare(strict_types=1);

use NorthBees\IvendiApi\Enums\CreditTier;
use NorthBees\IvendiApi\Enums\Endpoint;
use NorthBees\IvendiApi\Enums\VehicleClass;
use NorthBees\IvendiApi\Requests\PaymentSearchRequest;
use NorthBees\IvendiApi\Requests\QuoteConfigRequest;
use NorthBees\IvendiApi\Requests\QuoteRequest;

it('builds a quote request', function () {
    $payload = (new QuoteRequest(usedAsset(), cashPrice: 15900, cashDeposit: 2000, term: 48, annualDistance: 10000, quoteeId: 'Q1'))->toArray();

    expect($payload)->toBe([
        'mode' => 0,
        'quoteeId' => 'Q1',
        'currency' => 'GBP',
        'cashPrice' => 15900.0,
        'cashDeposit' => 2000.0,
        'cashBack' => 0.0,
        'term' => 48,
        'annualDistance' => 10000,
        'annualDistanceUnits' => 'miles',
        'asset' => [
            'isNew' => false,
            'currentOdometerReading' => 25000,
            'odometerUnits' => 'miles',
            'registrationDate' => '2021-05-30',
            'vatIncluded' => true,
            'vatQualifying' => false,
            'vehicleClass' => 1,
            'identifiers' => [['name' => 'capId', 'value' => '90132'], ['name' => 'vrm', 'value' => 'AB12CDE']],
        ],
    ]);
});

it('builds a payment search request', function () {
    $request = new PaymentSearchRequest(usedAsset(), 15900, terms: [36, 48], annualMileages: [10000], deposits: [0, 1000]);

    expect($request->toArray()['parameters'])->toBe([
        'termsInMonths' => [36, 48],
        'annualMileages' => [10000],
        'deposits' => [0, 1000],
        'creditTiers' => [''],
    ])->and($request->toArray())->not->toHaveKey('quoteeId');

    $tiered = new PaymentSearchRequest(usedAsset(), 15900, [48], [10000], [1000], [CreditTier::Excellent, CreditTier::Good]);

    expect($tiered->toArray()['parameters']['creditTiers'])->toBe(['Tier1', 'Tier3']);
});

it('sends the named vehicle class and fewer asset fields to quote config', function () {
    $payload = (new QuoteConfigRequest(usedAsset(), 15900, 'Q1'))->toArray();

    expect($payload['asset'])->toBe([
        'currentOdometerReading' => 25000,
        'odometerUnits' => 'miles',
        'registrationDate' => '2021-05-30',
        'vehicleClass' => 'Car',
        'identifiers' => [['name' => 'capId', 'value' => '90132'], ['name' => 'vrm', 'value' => 'AB12CDE']],
    ]);
});

it('labels vehicle classes', function () {
    expect(VehicleClass::Lcv->label())->toBe('LCV')->and(VehicleClass::Lcv->value)->toBe(2);
});

it('resolves endpoints from method and path', function (string $method, string $path, ?Endpoint $expected) {
    expect(Endpoint::fromRequest($method, $path))->toBe($expected);
})->with([
    ['POST', '/v1/quote-config', Endpoint::QuoteConfig],
    ['POST', '/v1/quotes', Endpoint::CreateQuotes],
    ['GET', '/v1/quotes/abc-123', Endpoint::FindQuote],
    ['POST', '/v1/payment-search', Endpoint::PaymentSearch],
    ['GET', '/v1/retailers/R1/representative-example', Endpoint::RepresentativeExample],
    ['POST', '/v1/applications', null],
]);
