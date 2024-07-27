<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\Service;

use App\Application\Infrastructure\Exception\BankAccountNotFoundException;
use App\Application\Port\Primary\TransactionValidatorInterface;
use App\Application\Port\Secondary\BankAccountRepositoryInterface;
use App\Application\Port\Secondary\UserInterface;

class TransactionValidator implements TransactionValidatorInterface
{
    public function __construct(
        protected BankAccountRepositoryInterface $bankAccountRepository,
    ) {
    }

    public function ownsSelectedBankAccount(UserInterface $owner, int $bankAccountId): bool
    {
        try {
            $bankAccount = $this->bankAccountRepository->get($bankAccountId);

            if ($bankAccount->getOwner() === $owner) {
                return false;
            }
        } catch (BankAccountNotFoundException) {
            return false;
        }

        return true;
    }
}
