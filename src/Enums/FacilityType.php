<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Enums;

/**
 * Finance product (facility) types.
 */
enum FacilityType: string
{
    case HirePurchase = 'HP';
    case PersonalContractPurchase = 'PCP';
    case ConditionalSale = 'CS';
    case LeasePurchase = 'LP';
    case PersonalContractHire = 'PCH';
    case BusinessContractHire = 'BCH';

    public static function fromKey(?string $key): ?self
    {
        return $key === null ? null : self::tryFrom(strtoupper(trim($key)));
    }
}
