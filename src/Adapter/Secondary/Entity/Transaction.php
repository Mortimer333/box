<?php

namespace App\Adapter\Secondary\Entity;

use App\Adapter\Secondary\Repository\TransactionRepository;
use App\Application\Port\Secondary\BankAccountInterface;
use App\Application\Port\Secondary\TransactionInterface;
use App\Domain\TransactionStatusEnum;
use App\Domain\TransactionTypeEnum;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transaction implements TransactionInterface
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $amount = null;

    #[ORM\ManyToOne(inversedBy: 'debtTransactions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?BankAccount $sender = null;

    #[ORM\Column(length: 255)]
    private ?string $receiverAccountNumber = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $receiver = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(enumType: TransactionTypeEnum::class)]
    private ?TransactionTypeEnum $type = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created = null;

    #[ORM\Column(enumType: TransactionStatusEnum::class)]
    private ?TransactionStatusEnum $status = null;

    /** @var int Amount of times that transaction was retried without success */
    #[ORM\Column]
    private int $retries;

    public function __construct()
    {
        $this->created = new \DateTime();
        $this->retries = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getSender(): ?BankAccountInterface
    {
        return $this->sender;
    }

    public function setSender(?BankAccountInterface $sender): static
    {
        $this->sender = $sender;

        return $this;
    }

    public function getReceiverAccountNumber(): ?string
    {
        return $this->receiverAccountNumber;
    }

    public function setReceiverAccountNumber(string $receiverAccountNumber): static
    {
        $this->receiverAccountNumber = $receiverAccountNumber;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getReceiver(): ?string
    {
        return $this->receiver;
    }

    public function setReceiver(string $receiver): static
    {
        $this->receiver = $receiver;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getType(): ?TransactionTypeEnum
    {
        return $this->type;
    }

    public function setType(TransactionTypeEnum $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): static
    {
        $this->created = $created;

        return $this;
    }

    public function getStatus(): ?TransactionStatusEnum
    {
        return $this->status;
    }

    public function setStatus(TransactionStatusEnum $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getRetries(): int
    {
        return $this->retries;
    }

    public function setRetries(int $retries): static
    {
        $this->retries = $retries;

        return $this;
    }
}
