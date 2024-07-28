<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\COR\TransferChain;

use App\Application\Infrastructure\Exception\InvalidLinkCallException;
use App\Application\Port\Secondary\BankAccountInterface;
use App\Application\Port\Secondary\BankAccountRepositoryInterface;
use App\Application\Port\Secondary\DatabaseManagerInterface;
use App\Application\Port\Secondary\StoreTransactionRepositoryInterface;
use App\Application\Port\Secondary\TransactionChainLinkInterface;
use App\Domain\CurrencyEnum;
use App\Domain\CurrencyMismatchException;
use App\Domain\TransactionStatusEnum;
use App\Domain\TransactionTypeEnum;
use App\Domain\Transfer;

final readonly class InternalTransfer implements TransactionChainLinkInterface
{
    public function __construct(
        private TransactionChainLinkInterface $next,
        private StoreTransactionRepositoryInterface $storeTransactionRepository,
        private DatabaseManagerInterface $databaseManager,
        private BankAccountRepositoryInterface $bankAccountRepository,
    ) {
    }

    public function process(
        Transfer $transfer,
        BankAccountInterface $sender,
    ): void {
        $receiver = $this->bankAccountRepository->getByIdentifier($transfer->receiver->bankAccountNumber);
        if (!$receiver) {
            throw new InvalidLinkCallException('Internal Transaction link was called with not internal receiver');
        }

        /** @var BankAccountInterface $receiver */
        $receiver = $this->bankAccountRepository->lockOptimistic((int) $receiver->getId());

        /** @var CurrencyEnum $currency */
        $currency = $receiver->getCurrency();
        if (!$transfer->doesCurrencyMatch($currency)) {
            throw new CurrencyMismatchException($transfer->currency, $currency);
        }

        $transfer->type = TransactionTypeEnum::Internal;
        $transaction = $this->storeTransactionRepository->create($transfer, $sender);
        $transaction->setStatus(TransactionStatusEnum::Finished);

        $senderCredit = (float) $sender->getCredit();
        $receiverCredit = (float) $receiver->getCredit();
        $transfer->transferFounds($senderCredit, $receiverCredit);
        $sender->setCredit($senderCredit);
        $receiver->setCredit($receiverCredit);

        $this->databaseManager->persist();

        $this->next->process($transfer, $sender);
    }
}
