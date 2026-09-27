<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Data;

use Illuminate\Support\Collection;
use NorthBees\IvendiApi\Support\Value;

/**
 * An error or warning. iVendi returns these as strings or as objects, depending on the endpoint.
 */
final readonly class IvendiError
{
    public function __construct(
        public string $message,
        public ?string $code = null,
        public ?string $field = null,
    ) {}

    public static function from(mixed $error): ?self
    {
        if (is_string($error) && trim($error) !== '') {
            return new self(trim($error));
        }

        if (! is_array($error)) {
            return null;
        }

        $message = Value::string($error, 'message') ?? Value::string($error, 'Message') ?? Value::string($error, 'description');

        if ($message === null) {
            return null;
        }

        return new self(
            message: $message,
            code: Value::string($error, 'code') ?? Value::string($error, 'Code') ?? Value::string($error, 'number') ?? Value::string($error, 'Number'),
            field: Value::string($error, 'field') ?? Value::string($error, 'property'),
        );
    }

    /**
     * Errors from a list, a single error, or an {publicErrors: [...]} style object.
     *
     * @return Collection<int, self>
     */
    public static function collect(mixed $errors, string $publicKey = 'publicErrors'): Collection
    {
        if (is_array($errors) && array_key_exists($publicKey, $errors)) {
            $errors = $errors[$publicKey];
        }

        if (! is_array($errors) || (! array_is_list($errors) && self::from($errors) !== null)) {
            $errors = [$errors];
        }

        return collect($errors)->map(self::from(...))->filter()->values();
    }
}
