<?php

namespace App\Adapter\Secondary\Entity;

use App\Adapter\Secondary\Repository\BankAccountRepository;
use App\Application\Port\Secondary\BankAccountInterface;
use App\Application\Port\Secondary\TransactionInterface;
use App\Application\Port\Secondary\UserInterface;
use App\Domain\CurrencyEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BankAccountRepository::class)]
#[ORM\UniqueConstraint(
    name: 'account_number_uniq',
    columns: ['accountNumber']
)]
class BankAccount implements BankAccountInterface
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Version, ORM\Column]
    private int $version;

    #[ORM\Column(enumType: CurrencyEnum::class)]
    private ?CurrencyEnum $currency = null;

    #[ORM\Column]
    private ?float $credit = null;

    #[ORM\ManyToOne(inversedBy: 'bankAccounts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    /**
     * @inheritdoc
     */
    #[ORM\OneToMany(mappedBy: 'sender', targetEntity: Transaction::class)]
    private Collection $transactions;

    #[ORM\Column(length: 255)]
    private ?string $accountNumber = null;

    #[ORM\Column(nullable: true)]
    private ?float $reserved = null;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
        $this->version = 1;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCurrency(): ?CurrencyEnum
    {
        return $this->currency;
    }

    public function setCurrency(CurrencyEnum $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getCredit(): ?float
    {
        return $this->credit;
    }

    public function setCredit(float $credit): static
    {
        $this->credit = $credit;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?UserInterface $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function addTransaction(TransactionInterface $transaction): static
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setSender($this);
        }

        return $this;
    }

    public function removeTransaction(TransactionInterface $transaction): static
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getSender() === $this) {
                $transaction->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function getAccountNumber(): ?string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(string $accountNumber): static
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    public function getReserved(): ?float
    {
        return $this->reserved;
    }

    public function setReserved(?float $reserved): static
    {
        $this->reserved = $reserved;

        return $this;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version): static
    {
        $this->version = $version;

        return $this;
    }
}
