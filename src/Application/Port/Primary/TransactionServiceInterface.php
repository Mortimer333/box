<?php

declare(strict_types=1);

namespace App\Application\Port\Primary;

interface TransactionServiceInterface
{
    public function process(
        int $userId,
        int $senderAccountId,
        string $receiverAccountIdentifier,
        string $title,
        string $receiverName,
        float $amount,
        ?string $address = null,
    ): void;
}
