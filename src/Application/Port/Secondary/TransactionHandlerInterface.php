<?php

declare(strict_types=1);

namespace App\Application\Port\Secondary;

interface TransactionHandlerInterface
{
    public function handle(int $transactionId): void;
}
