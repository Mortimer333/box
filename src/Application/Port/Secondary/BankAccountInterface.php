<?php

declare(strict_types=1);

namespace App\Application\Port\Secondary;

use App\Domain\CurrencyEnum;

interface BankAccountInterface
{
    public function getId(): ?int;
    public function getCurrency(): ?CurrencyEnum;
    public function setCurrency(CurrencyEnum $currency): static;
    public function getCredit(): ?float;
    public function setCredit(float $credit): static;
    public function getOwner(): ?UserInterface;
    public function setOwner(?UserInterface $owner): static;
    public function addTransaction(TransactionInterface $transaction): static;
    public function removeTransaction(TransactionInterface $transaction): static;

    /**
     * @return iterable<int, TransactionInterface>
     */
    public function getTransactions(): iterable;
    public function getAccountNumber(): ?string;
    public function setAccountNumber(string $accountNumber): static;
    public function getReserved(): ?float;
    public function setReserved(?float $reserved): static;
    public function getVersion(): int;
    public function setVersion(int $version): static;
}
