<?php

declare(strict_types=1);

namespace App\Domain;

enum TransactionTypeEnum: string
{
    /**
     * When both accounts are in the system
     */
    case internal = 'internal';

    /**
     * When accounts are in different banks
     */
    case AccountToAccount = 'account-to-account';

    /**
     * When user is withdrawing in cash
     */
    case WithdrawByMachine = 'withdraw-by-machine';
}
