<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Exceptions;

/**
 * No API key, base URL or quotee ID was configured or supplied.
 */
class IvendiMissingConfigurationException extends IvendiException {}
