<?php

declare(strict_types=1);

namespace App\Application\Port\Secondary;

interface TransactionValidatorInterface
{
    public function ownsSelectedBankAccount(UserInterface $user, int $bankAccountId): bool;
}
