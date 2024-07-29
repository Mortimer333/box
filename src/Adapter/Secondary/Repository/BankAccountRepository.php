<?php

namespace App\Adapter\Secondary\Repository;

use App\Adapter\Secondary\Entity\BankAccount;
use App\Application\Infrastructure\Exception\BankAccountNotFoundException;
use App\Application\Port\Secondary\BankAccountInterface;
use App\Application\Port\Secondary\BankAccountRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BankAccount>
 */
final class BankAccountRepository extends ServiceEntityRepository implements BankAccountRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BankAccount::class);
    }

    public function get(int $id): BankAccountInterface
    {
        $bankAccount = $this->find($id);
        if (!$bankAccount) {
            throw new BankAccountNotFoundException($id);
        }

        return $bankAccount;
    }

    public function getByIdentifier(string $accountNumber): ?BankAccountInterface
    {
        return $this->findOneBy([
            'accountNumber' => $accountNumber,
        ]);
    }

    public function lockOptimistic(int $id, ?int $version = null): ?BankAccountInterface
    {
        if (is_null($version)) {
            $account = $this->get($id);
            $this->_em->refresh($account);
            $version = $account->getVersion();
        }

        return $this->find($id, LockMode::OPTIMISTIC, $version);
    }
}
