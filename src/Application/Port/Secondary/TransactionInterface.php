<?php

declare(strict_types=1);

namespace App\Application\Port\Secondary;

use App\Domain\TransactionStatusEnum;
use App\Domain\TransactionTypeEnum;

interface TransactionInterface
{
    public function getId(): ?int;

    public function getAmount(): ?float;

    public function setAmount(float $amount): static;

    public function getSender(): ?BankAccountInterface;

    public function setSender(?BankAccountInterface $sender): static;

    public function getReceiverAccountNumber(): ?string;

    public function setReceiverAccountNumber(string $receiverAccountNumber): static;

    public function getTitle(): ?string;

    public function setTitle(string $title): static;

    public function getReceiver(): ?string;

    public function setReceiver(string $receiver): static;

    public function getAddress(): ?string;

    public function setAddress(string $address): static;

    public function getType(): ?TransactionTypeEnum;

    public function setType(TransactionTypeEnum $type): static;

    public function getCreated(): ?\DateTimeInterface;

    public function setCreated(\DateTimeInterface $created): static;

    public function getStatus(): ?TransactionStatusEnum;

    public function setStatus(TransactionStatusEnum $status): static;

    public function getCommissionFee(): ?float;

    public function setCommissionFee(float $commissionFee): static;
}
