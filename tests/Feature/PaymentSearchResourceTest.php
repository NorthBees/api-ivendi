<?php

declare(strict_types=1);

use NorthBees\IvendiApi\Enums\Endpoint;
use NorthBees\IvendiApi\Enums\FacilityType;
use NorthBees\IvendiApi\Facades\Ivendi as IvendiFacade;
use NorthBees\IvendiApi\Ivendi;
use NorthBees\IvendiApi\Requests\PaymentSearchRequest;
use NorthBees\IvendiApi\Testing\IvendiResponse;

it('searches payments across a matrix', function () {
    $fake = IvendiFacade::fake([Endpoint::PaymentSearch->value => IvendiResponse::paymentGrid([36, 48], [0, 1000], [10000])]);

    $result = app(Ivendi::class)->paymentSearch()->search(new PaymentSearchRequest(usedAsset(), 15000, [36, 48], [10000], [0, 1000]));

    $first = $result->rows->first();

    expect($result->rows)->toHaveCount(4)
        ->and($first?->term)->toBe(36)
        ->and($first?->deposit)->toBe(0.0)
        ->and($first?->annualMileage)->toBe(10000)
        ->and($first?->products->map->facilityType()->all())->toBe([FacilityType::HirePurchase, FacilityType::PersonalContractPurchase])
        ->and($first?->products->every->isQuoted())->toBeTrue();

    $fake->assertSent(Endpoint::PaymentSearch, fn (array $payload): bool => $payload['parameters']['deposits'] === [0, 1000] && $payload['cashPrice'] === 15000.0);
});

it('reads a representative example', function () {
    IvendiFacade::fake([Endpoint::RepresentativeExample->value => IvendiResponse::representativeExample(['apr' => 7.9])]);

    $example = app(Ivendi::class)->retailers()->representativeExample();

    expect($example->funderName)->toBe('Blackhorse Ltd')
        ->and($example->quote?->funderName)->toBe('Blackhorse Ltd')
        ->and($example->quote?->figures?->apr)->toBe(7.9)
        ->and($example->termDistance)->toBe(42000)
        ->and($example->isNew)->toBeFalse();
});
