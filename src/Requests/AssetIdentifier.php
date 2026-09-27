<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Requests;

use NorthBees\IvendiApi\Enums\AssetIdentifierType;

final readonly class AssetIdentifier
{
    public function __construct(
        public AssetIdentifierType $name,
        public string $value,
    ) {}

    public static function capId(int|string $capId): self
    {
        return new self(AssetIdentifierType::CapId, (string) $capId);
    }

    public static function capCode(string $capCode): self
    {
        return new self(AssetIdentifierType::CapCode, $capCode);
    }

    public static function vrm(string $vrm): self
    {
        return new self(AssetIdentifierType::Vrm, strtoupper(str_replace(' ', '', $vrm)));
    }

    /**
     * @return array{name: string, value: string}
     */
    public function toArray(): array
    {
        return ['name' => $this->name->value, 'value' => $this->value];
    }
}
