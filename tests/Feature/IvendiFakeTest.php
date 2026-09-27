<?php

declare(strict_types=1);

use NorthBees\IvendiApi\Enums\Endpoint;
use NorthBees\IvendiApi\Exceptions\IvendiRequestException;
use NorthBees\IvendiApi\Exceptions\IvendiValidationException;
use NorthBees\IvendiApi\Facades\Ivendi as IvendiFacade;
use NorthBees\IvendiApi\Ivendi;
use NorthBees\IvendiApi\Requests\PaymentSearchRequest;
use NorthBees\IvendiApi\Testing\IvendiResponse;

it('responds with closures that receive the payload', function () {
    $fake = IvendiFacade::fake([
        Endpoint::PaymentSearch->value => fn (array $payload): array => IvendiResponse::paymentGrid($payload['parameters']['termsInMonths'], $payload['parameters']['deposits'], $payload['parameters']['annualMileages'], ['HP']),
    ]);

    $result = app(Ivendi::class)->paymentSearch()->search(new PaymentSearchRequest(usedAsset(), 15000, [24, 36, 48], [10000], [0]));

    expect($result->rows)->toHaveCount(3);
    $fake->assertSentTimes(Endpoint::PaymentSearch, 1);
});

it('fails loudly for requests that were not faked', function () {
    IvendiFacade::fake();

    app(Ivendi::class)->retailers()->representativeExample();
})->throws(IvendiRequestException::class, 'was not faked');

it('serves error responses', function () {
    IvendiFacade::fake()->push(Endpoint::PaymentSearch, IvendiResponse::error(['cashPrice must be positive']));

    app(Ivendi::class)->paymentSearch()->search(new PaymentSearchRequest(usedAsset(), 15000, [48], [10000], [0]));
})->throws(IvendiValidationException::class, 'cashPrice must be positive');
