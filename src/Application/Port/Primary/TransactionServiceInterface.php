<?php

declare(strict_types=1);

namespace App\Application\Port\Primary;

use App\Application\Port\Secondary\UserInterface;

interface TransactionServiceInterface
{
    public function process(
        UserInterface $user,
        int $senderAccountId,
        string $receiverAccountIdentifier,
        string $title,
        string $receiverName,
        float $amount,
        ?string $address = null,
    ): void;
}
