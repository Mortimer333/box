<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\COR\TransferChain;

use App\Application\Port\Secondary\BankAccountInterface;
use App\Application\Port\Secondary\BankAccountRepositoryInterface;
use App\Application\Port\Secondary\TransactionChainLinkInterface;
use App\Domain\Transfer;

final readonly class DeterminateTransferType implements TransactionChainLinkInterface
{
    public function __construct(
        private TransactionChainLinkInterface $internalTransfer,
        protected TransactionChainLinkInterface $bankToBankTransaction,
        private BankAccountRepositoryInterface $bankAccountRepository,
    ) {
    }

    public function process(
        Transfer $transfer,
        BankAccountInterface $sender,
    ): void {
        $receiverBankAccount = $this->bankAccountRepository->getByIdentifier($transfer->receiver->bankAccountNumber);
        if ($receiverBankAccount) {
            $this->internalTransfer->process($transfer, $sender);

            return;
        }

        $this->bankToBankTransaction->process($transfer, $sender);
    }
}
