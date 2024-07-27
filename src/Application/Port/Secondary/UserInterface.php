<?php

declare(strict_types=1);

namespace App\Application\Port\Secondary;

interface UserInterface
{
    public function getId(): ?int;
    public function getEmail(): ?string;

    /**
     * @return iterable<int, BankAccountInterface>
     */
    public function getBankAccounts(): iterable;
    public function addBankAccount(BankAccountInterface $bankAccount): static;
    public function removeBankAccount(BankAccountInterface $bankAccount): static;
}
