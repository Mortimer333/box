<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\Exception;

class BankAccountNotFoundException extends \Exception
{
    public function __construct(
        int $userId,
    ) {
        parent::__construct(
            sprintf('Given bank account [%s] not found', $userId),
            404
        );
    }
}
