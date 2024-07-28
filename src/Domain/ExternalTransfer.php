<?php

declare(strict_types=1);

namespace App\Domain;

final class ExternalTransfer
{
    public function __construct(
        public readonly ExternalTransferSender $sender,
        public readonly float $amount,
        public readonly ?float $commissionFee = null,
    ) {
    }

    public function getCommissionFeeAmount(): float
    {
        if (!is_null($this->commissionFee)) {
            return $this->amount * $this->commissionFee;
        }

        if (!isset($_ENV['EXTERNAL_COMMISSION_FEE'])) {
            throw new ConfigurationException('Invalid configuration, missing external commission fee value');
        }

        return $this->amount * $_ENV['EXTERNAL_COMMISSION_FEE'];
    }

    public function senderHasEnoughCredit(): bool
    {
        return ($this->sender->credit - $this->sender->reserved) >= ($this->amount + $this->getCommissionFeeAmount());
    }

    public function finishFoundsTransfer(float &$credit, float &$reserved, float &$commissionCredit): void
    {
        $amountWithFee = $this->amount + $this->getCommissionFeeAmount();
        $credit -= $amountWithFee;
        $reserved -= $amountWithFee;
        $commissionCredit += $this->getCommissionFeeAmount();
    }
}
