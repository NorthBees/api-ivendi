<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use NorthBees\IvendiApi\Requests\Asset;
use NorthBees\IvendiApi\Requests\AssetIdentifier;
use NorthBees\IvendiApi\Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit', 'Live');

function usedAsset(): Asset
{
    return new Asset(
        isNew: false,
        currentOdometerReading: 25000,
        registrationDate: CarbonImmutable::parse('2021-05-30'),
        identifiers: [AssetIdentifier::capId(90132), AssetIdentifier::vrm('ab12 cde')],
    );
}
