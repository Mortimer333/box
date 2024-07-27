<?php

declare(strict_types=1);

namespace App\Domain;

final class Transfer
{
    public function __construct(
        public readonly Sender $sender,
        public readonly Receiver $receiver,
        public float $amount,
        public readonly string $title,
        public readonly TransactionTypeEnum $type,
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

        $this->amount *= $_ENV['COMMISSION_FEE'];
    }

    public function senderHasEnoughCredit(): bool
    {
        return $this->sender->bankAccountCredit >= $this->amount;
    }
}
