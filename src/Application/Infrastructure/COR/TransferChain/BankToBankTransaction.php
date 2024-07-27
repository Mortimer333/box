<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\COR\TransferChain;

use App\Application\Infrastructure\Message\ProcessTransactionMessage;
use App\Application\Port\Secondary\BankAccountInterface;
use App\Application\Port\Secondary\DatabaseManagerInterface;
use App\Application\Port\Secondary\TransactionRepositoryInterface;
use App\Domain\TransactionTypeEnum;
use App\Domain\Transfer;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class BankToBankTransaction
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepository,
        private FinishChain $finishChain,
        // @TODO prepare a proxy interface for dispatcher and link it by symfony config
        private MessageBusInterface $messageBus,
        private DatabaseManagerInterface $databaseManager,
    ) {
    }

    public function process(Transfer $transfer, BankAccountInterface $sender): void
    {
        $transfer->type = TransactionTypeEnum::BankToBank;
        $sender->setReserved($sender->getReserved() + $transfer->getAmount());
        $transaction = $this->transactionRepository->create($transfer, $sender);
        $this->databaseManager->persist();

        $this->messageBus->dispatch(new ProcessTransactionMessage((int) $transaction->getId()));

        $this->finishChain->process($transfer);
    }
}
