<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\COR\TransferChain;

use App\Application\Infrastructure\Exception\InvalidLinkCallException;
use App\Application\Infrastructure\Message\TransferCommissionFeeFoundsMessage;
use App\Application\Port\Secondary\BankAccountInterface;
use App\Application\Port\Secondary\BankAccountRepositoryInterface;
use App\Application\Port\Secondary\DatabaseManagerInterface;
use App\Application\Port\Secondary\MessageBusInterface;
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
        private MessageBusInterface $messageBus,
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

        /* @TODO maybe move to it's own link? But what are other uses then internal transfer? */
        /** @var CurrencyEnum $currency */
        $currency = $receiver->getCurrency();
        if (!$transfer->doesCurrencyMatch($currency)) {
            throw new CurrencyMismatchException($transfer->currency, $currency);
        }

        $transaction = $this->storeTransactionRepository->create(
            $sender,
            TransactionTypeEnum::Internal,
            $transfer->currency,
            $transfer->amount,
            $transfer->getCommissionFeeAmount(),
            $transfer->title,
            $transfer->receiver->name,
            $transfer->receiver->bankAccountNumber,
            $transfer->receiver->address,
        );
        $transaction->setStatus(TransactionStatusEnum::Finished);

        $senderCredit = (float) $sender->getCredit();
        $receiverCredit = (float) $receiver->getCredit();
        $commissionCredit = 0;
        $transfer->transferFounds($senderCredit, $receiverCredit, $commissionCredit);
        $sender->setCredit($senderCredit);
        $receiver->setCredit($receiverCredit);

        // It's very important to not change order of execution - first persist change, then message about new founds
        $this->databaseManager->persist();
        $this->messageBus->dispatch(new TransferCommissionFeeFoundsMessage($commissionCredit));

        $this->next->process($transfer, $sender);
    }
}
