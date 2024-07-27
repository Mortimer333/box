<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\COR\TransferChain;

use App\Application\Port\Secondary\BankAccountInterface;
use App\Application\Port\Secondary\BankAccountRepositoryInterface;
use App\Domain\Transfer;

final readonly class DeterminateTransferType
{
    public function __construct(
        private InternalTransfer $internalTransfer,
        private BankAccountRepositoryInterface $bankAccountRepository,
        private BankToBankTransaction $bankToBankTransaction,
    ) {
    }

    public function process(Transfer $transfer, BankAccountInterface $sender): void
    {
        $receiverBankAccount = $this->bankAccountRepository->getByIdentifier($transfer->receiver->bankAccountNumber);
        if ($receiverBankAccount) {
            /** @var BankAccountInterface $receiverBankAccount */
            $receiverBankAccount = $this->bankAccountRepository->lockOptimistic((int) $receiverBankAccount->getId());
            $this->internalTransfer->process($transfer, $sender, $receiverBankAccount);

            return;
        }

        $this->bankToBankTransaction->process($transfer, $sender);
    }
}
