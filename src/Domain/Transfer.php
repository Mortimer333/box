<?php

declare(strict_types=1);

namespace App\Domain;

final class Transfer
{
    public function __construct(
        public readonly Sender $sender,
        public readonly Receiver $receiver,
        public readonly string $title,
        public readonly CurrencyEnum $currency,
        public readonly float $amount,
    ) {
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDailyTransactionLimit(): int
    {
        return (int) (
            $_ENV['MAX_DAILY_TRANSACTION_LIMIT']
            ?? throw new ConfigurationException('Invalid configuration, missing maximum of daily transactions')
        );
    }

    public function senderHasEnoughCredit(): bool
    {
        return ($this->sender->credit - $this->sender->reserved) >= ($this->amount + $this->getCommissionFeeAmount());
    }

    public function getCommissionFeeAmount(): float
    {
        if (!isset($_ENV['COMMISSION_FEE'])) {
            throw new ConfigurationException('Invalid configuration, missing commission fee value');
        }

        return $this->amount * $_ENV['COMMISSION_FEE'];
    }

    /**
     * @codeCoverageIgnore
     */
    public function doesCurrencyMatch(CurrencyEnum $receiverCurrency): bool
    {
        return $receiverCurrency === $this->currency;
    }

    /**
     * @codeCoverageIgnore
     */
    public function hasClientReachedHisDailyLimit(): bool
    {
        return $this->getDailyTransactionLimit() <= $this->sender->transactionsDoneToday;
    }

    public function transferFounds(float &$sender, float &$receiver, float &$commissionCredit): void
    {
        $feeAmount = $this->getCommissionFeeAmount();
        $sender -= ($this->amount + $feeAmount);
        $receiver += $this->amount;
        $commissionCredit += $feeAmount;
    }

    /**
     * @codeCoverageIgnore
     */
    public function convertToExternal(): ExternalTransfer
    {
        return new ExternalTransfer(
            new ExternalTransferSender(
                $this->sender->bankAccountNumber,
                $this->sender->credit,
                $this->sender->reserved,
            ),
            $this->amount,
        );
    }
}
