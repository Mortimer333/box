<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\Service;

use App\Application\Infrastructure\Exception\TransactionNotFoundException;
use App\Application\Infrastructure\Message\TransferCommissionFeeFoundsMessage;
use App\Application\Port\Secondary\BankAccountInterface;
use App\Application\Port\Secondary\BankAccountRepositoryInterface;
use App\Application\Port\Secondary\DatabaseManagerInterface;
use App\Application\Port\Secondary\ExternalBankClientInterface;
use App\Application\Port\Secondary\MessageBusInterface;
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
        protected MessageBusInterface $messageBus,
        protected ExternalBankClientInterface $externalBankClient,
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
            (int) $transaction->getSender()?->getId(),
            $transaction->getSender()?->getVersion(),
        );

        $transfer = new ExternalTransfer(
            new ExternalTransferSender(
                (string) $sender->getAccountNumber(),
                (float) $sender->getCredit(),
                (float) $sender->getReserved(),
            ),
            (float) $transaction->getAmount(),
            (float) $transaction->getCommissionFee(),
        );

        $this->externalBankClient->transfer($transfer);

        $credit = (float) $sender->getCredit();
        $reserved = (float) $sender->getReserved();

        $feeCredit = 0;
        $transfer->finishFoundsTransfer($credit, $reserved, $feeCredit);

        $sender->setCredit($credit);
        $sender->setReserved($reserved);

        $transaction->setStatus(TransactionStatusEnum::Finished);

        // It's very important to not change order of execution - first persist change, then message about new founds
        $this->databaseManager->persist();
        $this->messageBus->dispatch(new TransferCommissionFeeFoundsMessage($feeCredit));
    }
}
