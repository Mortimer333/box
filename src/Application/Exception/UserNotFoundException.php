<?php

declare(strict_types=1);

namespace App\Application\Exception;

/**
 * @codeCoverageIgnore
 */
final class UserNotFoundException extends \Exception
{
    public function __construct(
        int $userId,
    ) {
        parent::__construct(
            sprintf('Given user [%s] not found', $userId),
            404
        );
    }
}
