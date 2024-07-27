<?php

declare(strict_types=1);

namespace App\Application\Port\Primary;

use App\Application\Port\Secondary\UserInterface;

interface TransactionValidatorInterface
{
    public function ownsSelectedBankAccount(UserInterface $user, int $bankAccountId): bool;
}
