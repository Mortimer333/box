<?php

declare(strict_types=1);

namespace App\Application\Port\Secondary;

use App\Domain\Transfer;

interface StoreTransactionRepositoryInterface
{
    public function create(Transfer $transfer, BankAccountInterface $sender): TransactionInterface;
}
