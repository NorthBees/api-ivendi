<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Enums;

/**
 * Credit tiers for lenders with risk based pricing.
 */
enum CreditTier: string
{
    case None = 'None';
    case Excellent = 'Tier1';
    case VeryGood = 'Tier2';
    case Good = 'Tier3';
    case Fair = 'Tier4';
    case BelowAverage = 'Tier5';
}
