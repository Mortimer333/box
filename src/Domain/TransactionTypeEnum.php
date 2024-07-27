<?php

declare(strict_types=1);

namespace App\Domain;

enum TransactionTypeEnum: string
{
    /**
     * When both accounts are in the system.
     */
    case Internal = 'internal';

    /**
     * When accounts are in different banks.
     */
    case BankToBank = 'bank-to-bank';
}
