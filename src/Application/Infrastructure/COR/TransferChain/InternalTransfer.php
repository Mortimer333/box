<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\COR\TransferChain;

use App\Application\Port\Secondary\BankAccountInterface;
use App\Application\Port\Secondary\DatabaseManagerInterface;
use App\Application\Port\Secondary\TransactionRepositoryInterface;
use App\Domain\CurrencyEnum;
use App\Domain\CurrencyMismatchException;
use App\Domain\TransactionStatusEnum;
use App\Domain\TransactionTypeEnum;
use App\Domain\Transfer;

final readonly class InternalTransfer
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepository,
        private FinishChain $finishChain,
        private DatabaseManagerInterface $databaseManager,
    ) {
    }

    public function process(Transfer $transfer, BankAccountInterface $sender, BankAccountInterface $receiver): void
    {
        /** @var CurrencyEnum $currency */
        $currency = $receiver->getCurrency();
        if (!$transfer->doCurrencyMatch($currency)) {
            throw new CurrencyMismatchException($transfer->currency, $currency);
        }

        $transfer->type = TransactionTypeEnum::Internal;
        $transaction = $this->transactionRepository->create($transfer, $sender);
        $transaction->setStatus(TransactionStatusEnum::Finished);
        $sender->setCredit($sender->getCredit() - $transfer->getAmount());
        $receiver->setCredit($receiver->getCredit() + $transfer->getAmount());
        $this->databaseManager->persist();

        $this->finishChain->process($transfer);
    }
}
