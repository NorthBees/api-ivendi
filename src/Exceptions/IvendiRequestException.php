<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Exceptions;

use Illuminate\Support\Collection;
use NorthBees\IvendiApi\Data\IvendiError;
use NorthBees\IvendiApi\Enums\Endpoint;

/**
 * The API rejected the request (4xx), or returned errors in the response envelope.
 */
class IvendiRequestException extends IvendiException
{
    /**
     * @param  Collection<int, IvendiError>  $errors
     */
    public function __construct(
        string $message,
        ?Endpoint $endpoint = null,
        public readonly Collection $errors = new Collection,
        public readonly ?int $status = null,
    ) {
        parent::__construct($message, $endpoint, $status ?? 0);
    }
}
