<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\Exception;

final class TransactionNotFoundException extends \Exception
{
    public function __construct(
        int $transactionId,
    ) {
        parent::__construct(
            sprintf('Given transaction [%s] not found', $transactionId),
            404
        );
    }
}
