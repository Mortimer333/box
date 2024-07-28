<?php

namespace App\Adapter\Secondary\Entity;

use App\Adapter\Secondary\Repository\BankAccountRepository;
use App\Application\Port\Secondary\BankAccountInterface;
use App\Domain\CurrencyEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @codeCoverageIgnore
 */
#[ORM\Entity(repositoryClass: BankAccountRepository::class)]
#[ORM\UniqueConstraint(
    name: 'account_number_uniq',
    columns: ['account_number']
)]
class BankAccount implements BankAccountInterface
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Version, ORM\Column(type: 'integer')]
    private int $version;

    #[ORM\Column(enumType: CurrencyEnum::class)]
    private ?CurrencyEnum $currency = null;

    #[ORM\Column]
    private ?float $credit = null;

    #[ORM\ManyToOne(inversedBy: 'bankAccounts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\OneToMany(mappedBy: 'sender', targetEntity: Transaction::class)]
    private Collection $transactions;

    #[ORM\Column(length: 255)]
    private ?string $accountNumber = null;

    #[ORM\Column(nullable: true)]
    private ?float $reserved;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
        $this->version = 1;
        $this->reserved = 0.0;
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

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function addTransaction(Transaction $transaction): static
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setSender($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): static
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
