<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Exceptions;

/**
 * The API could not be reached, or returned a 5xx response after retries.
 */
class IvendiConnectionException extends IvendiException {}
