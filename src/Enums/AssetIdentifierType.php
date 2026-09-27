<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Enums;

/**
 * How an asset is identified. A CAP taxonomy code (capId/capCode) is needed for residual
 * value based products such as PCP; a VRM triggers a (chargeable) UK vehicle lookup.
 */
enum AssetIdentifierType: string
{
    case CapId = 'capId';
    case CapCode = 'capCode';
    case Vrm = 'vrm';
}
