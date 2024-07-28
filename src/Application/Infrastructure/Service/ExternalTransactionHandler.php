<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\Service;

use App\Application\Infrastructure\Exception\TransactionNotFoundException;
use App\Application\Port\Secondary\BankAccountInterface;
use App\Application\Port\Secondary\BankAccountRepositoryInterface;
use App\Application\Port\Secondary\DatabaseManagerInterface;
use App\Application\Port\Secondary\RetrieveTransactionRepositoryInterface;
use App\Application\Port\Secondary\TransactionHandlerInterface;
use App\Domain\ExternalTransfer;
use App\Domain\ExternalTransferSender;
use App\Domain\TransactionStatusEnum;

/**
 * Mock handler to stimulate some type of integration with external banking systems.
 */
final readonly class ExternalTransactionHandler implements TransactionHandlerInterface
{
    public function __construct(
        protected RetrieveTransactionRepositoryInterface $retrieveTransactionRepository,
        protected BankAccountRepositoryInterface $bankAccountRepository,
        protected DatabaseManagerInterface $databaseManager,
    ) {
    }

    /**
     * @throws TransactionNotFoundException
     */
    public function handle(int $transactionId): void
    {
        $transaction = $this->retrieveTransactionRepository->get($transactionId);
        $transaction->setStatus(TransactionStatusEnum::Awaiting);
        $this->databaseManager->persist();

        /** @var BankAccountInterface $sender */
        $sender = $this->bankAccountRepository->lockOptimistic(
            (int) $sender->getId(),
            $transaction->getSender()?->getVersion(),
        );

        $transfer = new ExternalTransfer(
            new ExternalTransferSender(
                (string) $sender->getAccountNumber(),
                (float) $sender->getCredit(),
            ),
            (float) $transaction->getAmount(),
        );

        // Some long process which allows us to easily test race condition handling, and gives window for data to change
        // in DB
        usleep(100);

        $credit = (float) $sender->getCredit();
        $reserved = (float) $sender->getReserved();

        $transfer->finishFoundsTransfer($credit, $reserved);

        $sender->setCredit($credit);
        $sender->setReserved($reserved);

        $transaction->setStatus(TransactionStatusEnum::Finished);

        $this->databaseManager->persist();
    }
}
