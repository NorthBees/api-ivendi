<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Exceptions;

/**
 * The API key was rejected (HTTP 401 or 403).
 */
class IvendiAuthenticationException extends IvendiRequestException {}
