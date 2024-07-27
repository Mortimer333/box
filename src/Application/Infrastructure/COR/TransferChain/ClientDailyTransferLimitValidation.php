<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\COR\TransferChain;

use App\Application\Port\Secondary\BankAccountInterface;
use App\Application\Port\Secondary\TransactionRepositoryInterface;
use App\Domain\DailyLimitExceededException;
use App\Domain\Transfer;

final readonly class ClientDailyTransferLimitValidation
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepository,
        private DeterminateTransferType $determinateTransferType,
    ) {
    }

    public function process(Transfer $transfer, BankAccountInterface $sender): void
    {
        // Not really sure about this implementation. It should be moved into the Domain as biznes requirement,
        // but can't really see that happening without passing persistence layer to Domain which is worse.
        $now = new \DateTime();
        $dailyLimit = $transfer->getLimitOfDailyTransaction();
        $transactions = $this->transactionRepository->retrieveBetweenDate(
            new \DateTime($now->format('Y-m-d') . ' 00:00:00'),
            new \DateTime($now->format('Y-m-d') . ' 23:59:59'),
            $dailyLimit,
        );

        if (count($transactions) >= $dailyLimit) {
            throw new DailyLimitExceededException($dailyLimit);
        }

        $this->determinateTransferType->process($transfer, $sender);
    }
}
