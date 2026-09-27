<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Requests;

use DateTimeInterface;
use NorthBees\IvendiApi\Enums\OdometerUnit;
use NorthBees\IvendiApi\Enums\VehicleClass;
use NorthBees\IvendiApi\Requests\Concerns\HasExtraFields;

/**
 * The vehicle being quoted for.
 */
final readonly class Asset
{
    use HasExtraFields;

    /**
     * @param  list<AssetIdentifier>  $identifiers  at least one; a CAP code is needed for residual value based products
     * @param  bool  $vatIncluded  whether the cash price includes VAT
     * @param  bool  $vatQualifying  whether the asset is VAT qualifying (normally commercial vehicles)
     * @param  list<array<string, mixed>>  $valueAddedProducts  items set up for the retailer in iVendi TRANSACT
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public bool $isNew,
        public int $currentOdometerReading,
        public DateTimeInterface $registrationDate,
        public array $identifiers,
        public bool $vatIncluded = true,
        public bool $vatQualifying = false,
        public VehicleClass $vehicleClass = VehicleClass::Car,
        public OdometerUnit $odometerUnits = OdometerUnit::Miles,
        public array $valueAddedProducts = [],
        public array $extra = [],
    ) {}

    /**
     * The asset for quotes and payment search.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->payload([
            'isNew' => $this->isNew,
            'currentOdometerReading' => $this->currentOdometerReading,
            'odometerUnits' => $this->odometerUnits->value,
            'registrationDate' => $this->registrationDate->format('Y-m-d'),
            'vatIncluded' => $this->vatIncluded,
            'vatQualifying' => $this->vatQualifying,
            'vehicleClass' => $this->vehicleClass->value,
            'identifiers' => $this->identifierPayload(),
            'valueAddedProducts' => $this->valueAddedProducts,
        ]);
    }

    /**
     * The asset for quote config, which takes fewer fields and a named vehicle class.
     *
     * @return array<string, mixed>
     */
    public function toConfigArray(): array
    {
        return $this->payload([
            'currentOdometerReading' => $this->currentOdometerReading,
            'odometerUnits' => $this->odometerUnits->value,
            'registrationDate' => $this->registrationDate->format('Y-m-d'),
            'vehicleClass' => $this->vehicleClass->label(),
            'identifiers' => $this->identifierPayload(),
        ]);
    }

    /**
     * @return list<array{name: string, value: string}>
     */
    private function identifierPayload(): array
    {
        return array_map(fn (AssetIdentifier $identifier): array => $identifier->toArray(), $this->identifiers);
    }
}
