<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Exceptions;

use DomainException;

final class SuperAdministratorAlreadyExistsException extends DomainException {}
