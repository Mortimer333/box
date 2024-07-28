<?php

declare(strict_types=1);

namespace App\Domain;

final class Transfer
{
    private bool $commissionFeeApplied = false;

    public function __construct(
        public readonly Sender $sender,
        public readonly Receiver $receiver,
        public readonly string $title,
        public readonly CurrencyEnum $currency,
        protected float $amount,
        public ?TransactionTypeEnum $type = null,
    ) {
    }

    public function getDailyTransactionLimit(): int
    {
        return $_ENV['MAX_DAILY_TRANSACTION_LIMIT']
            ?? throw new ConfigurationException('Invalid configuration, missing maximum of daily transactions')
        ;
    }

    public function applyCommissionFee(): void
    {
        // Make this one time action
        if ($this->commissionFeeApplied) {
            return;
        }
        $this->commissionFeeApplied = true;

        if (!isset($_ENV['COMMISSION_FEE'])) {
            throw new ConfigurationException('Invalid configuration, missing commission fee value');
        }

        $this->amount *= $_ENV['COMMISSION_FEE'];
    }

    public function senderHasEnoughCredit(): bool
    {
        return $this->sender->bankAccountCredit >= $this->amount;
    }

    public function doesCurrencyMatch(CurrencyEnum $receiverCurrency): bool
    {
        return $receiverCurrency === $this->currency;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function hasClientReachedHisDailyLimit(): bool
    {
        return $this->getDailyTransactionLimit() <= $this->sender->transactionsDoneToday;
    }

    public function transferFounds(float &$sender, float &$receiver): void
    {
        $sender -= $this->amount;
        $receiver += $this->amount;
    }
}
