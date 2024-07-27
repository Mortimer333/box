<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\Service;

use App\Application\Infrastructure\Exception\TransactionNotFoundException;
use App\Application\Port\Secondary\BankAccountInterface;
use App\Application\Port\Secondary\BankAccountRepositoryInterface;
use App\Application\Port\Secondary\DatabaseManagerInterface;
use App\Application\Port\Secondary\TransactionRepositoryInterface;
use App\Domain\TransactionStatusEnum;

/**
 * Mock handler to stimulate some type of integration with external banking systems.
 */
class ExternalTransactionHandler
{
    public function __construct(
        protected TransactionRepositoryInterface $transactionRepository,
        protected BankAccountRepositoryInterface $bankAccountRepository,
        protected DatabaseManagerInterface $databaseManager,
    ) {
    }

    /**
     * @throws TransactionNotFoundException
     */
    public function handle(int $transactionId): void
    {
        $transaction = $this->transactionRepository->get($transactionId);
        $transaction->setStatus(TransactionStatusEnum::Awaiting);
        $this->databaseManager->persist();
        usleep(100);

        // Retrieving again to put it in doctrine UoW pool/persisting after flush - library specific behaviour
        $transaction = $this->transactionRepository->get($transactionId);
        /** @var BankAccountInterface $sender */
        $sender = $transaction->getSender();
        /** @var BankAccountInterface $senderBankAccount */
        $senderBankAccount = $this->bankAccountRepository->lockOptimistic((int) $sender->getId());
        $senderBankAccount->setCredit($senderBankAccount->getCredit() - $transaction->getAmount());
        $senderBankAccount->setReserved($senderBankAccount->getReserved() - $transaction->getAmount());
        $transaction->setStatus(TransactionStatusEnum::Finished);
        $this->databaseManager->persist();
    }
}
