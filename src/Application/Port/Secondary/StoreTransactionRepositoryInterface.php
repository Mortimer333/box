<?php

declare(strict_types=1);

namespace App\Application\Port\Secondary;

use App\Domain\CurrencyEnum;
use App\Domain\TransactionTypeEnum;

interface StoreTransactionRepositoryInterface
{
    public function create(
        BankAccountInterface $sender,
        TransactionTypeEnum $type,
        CurrencyEnum $currency,
        float $amount,
        float $commissionFee,
        string $title,
        string $receiver,
        string $receiverAccountNumber,
        ?string $address = null,
    ): TransactionInterface;
}
