<?php

declare(strict_types=1);

namespace App\Domain;

use App\Adapter\Secondary\Entity\Transaction;

final readonly class Transfer
{
    public Transaction $transaction;

    public function __construct(
        public Sender $sender,
        public Receiver $receiver,
        public float $amount,
        public string $title,
        public TransactionTypeEnum $type,
    ) {
    }

    // @TODO move this to Domain Service - TransferService
    public function send(): void {
        // Send transaction to message queue to be processed
    }

    public function applyCommissionFee(): void
    {
        // @TODO catch this exception and return 500 response
        if (!isset($_ENV['COMMISSION_FEE'])) {
            throw new \DomainException('Invalid configuration, missing commission fee value');
        }

        $this->transaction->setAmount($this->transaction->getAmount() * $_ENV['COMMISSION_FEE']);
    }

    public function senderHasEnoughCredit(): bool
    {
        return ((int) $this->sender->bankAccount->getCredit()) >= ((int) $this->transaction->getAmount());
    }
}
