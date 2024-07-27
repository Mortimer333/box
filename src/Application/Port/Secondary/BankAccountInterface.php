<?php

declare(strict_types=1);

namespace App\Application\Port\Secondary;

use App\Domain\CurrencyEnum;

interface BankAccountInterface
{
    public function getId(): ?int;

    public function getCurrency(): ?CurrencyEnum;

    public function getCredit(): ?float;

    public function setCredit(float $credit): static;

    public function getOwner(): ?UserInterface;

    /**
     * @return iterable<int, TransactionInterface>
     */
    public function getTransactions(): iterable;

    public function getAccountNumber(): ?string;

    public function getReserved(): ?float;

    public function setReserved(?float $reserved): static;

    public function getVersion(): int;
}
