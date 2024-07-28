<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\COR\TransferChain;

use App\Application\Infrastructure\Message\ProcessTransactionMessage;
use App\Application\Port\Secondary\BankAccountInterface;
use App\Application\Port\Secondary\DatabaseManagerInterface;
use App\Application\Port\Secondary\MessageBusInterface;
use App\Application\Port\Secondary\StoreTransactionRepositoryInterface;
use App\Application\Port\Secondary\TransactionChainLinkInterface;
use App\Domain\TransactionTypeEnum;
use App\Domain\Transfer;

final readonly class BankToBankTransaction implements TransactionChainLinkInterface
{
    public function __construct(
        private TransactionChainLinkInterface $next,
        protected StoreTransactionRepositoryInterface $storeTransactionRepositoryInterface,
        private MessageBusInterface $messageBus,
        private DatabaseManagerInterface $databaseManager,
    ) {
    }

    public function process(
        Transfer $transfer,
        BankAccountInterface $sender,
    ): void {
        $file = fopen('/app/var/test', 'a');
        fwrite($file, 'bank to bank' . PHP_EOL);
        fclose($file);
        $external = $transfer->convertToExternal();
        $sender->setReserved($sender->getReserved() + $external->amount);
        $transaction = $this->storeTransactionRepositoryInterface->create(
            $sender,
            TransactionTypeEnum::BankToBank,
            $transfer->currency,
            $external->amount,
            $external->getCommissionFeeAmount(),
            $transfer->title,
            $transfer->receiver->name,
            $transfer->receiver->bankAccountNumber,
            $transfer->receiver->address,
        );
        $this->databaseManager->persist();

        $this->messageBus->dispatch(new ProcessTransactionMessage((int) $transaction->getId()));

        $this->next->process($transfer, $sender);
    }
}
