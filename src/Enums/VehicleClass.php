<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Enums;

/**
 * The asset class. Quotes and payment search take the number; quote config takes the name.
 */
enum VehicleClass: int
{
    case Car = 1;
    case Lcv = 2;
    case Motorbike = 3;

    public function label(): string
    {
        return match ($this) {
            self::Car => 'Car',
            self::Lcv => 'LCV',
            self::Motorbike => 'Motorbike',
        };
    }
}
