<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Exceptions;

use NorthBees\IvendiApi\Enums\Endpoint;
use RuntimeException;
use Throwable;

/**
 * Base exception for every error raised by the iVendi SDK. Messages never contain credentials.
 */
class IvendiException extends RuntimeException
{
    public function __construct(
        string $message = '',
        public readonly ?Endpoint $endpoint = null,
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
