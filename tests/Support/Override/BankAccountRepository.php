<?php

declare(strict_types=1);

namespace App\Tests\Support\Override;

use App\Application\Port\Secondary\BankAccountInterface;
use App\Application\Port\Secondary\BankAccountRepositoryInterface;
use Doctrine\Persistence\ObjectRepository;

class BankAccountRepository implements BankAccountRepositoryInterface, ObjectRepository
{
    public static ?BankAccountRepositoryInterface $mock = null;

    public function __construct(
        protected BankAccountRepositoryInterface $default,
    ) {
    }

    public function __call(string $name, array $arguments): mixed
    {
        return call_user_func_array([$this->getManager(), $name], $arguments);
    }

    public function getManager(): BankAccountRepositoryInterface
    {
        return self::$mock ?? $this->default;
    }

    public function get(int $id): BankAccountInterface
    {
        return $this->getManager()->get($id);
    }

    public function getByIdentifier(string $accountNumber): ?BankAccountInterface
    {
        return $this->getManager()->getByIdentifier($accountNumber);
    }

    public function lockOptimistic(int $id, ?int $version = null): ?BankAccountInterface
    {
        return $this->getManager()->lockOptimistic($id, $version);
    }

    public function find($id)
    {
        return $this->getManager()->find($id);
    }

    public function findAll()
    {
        return $this->getManager()->findAll();
    }

    public function findBy(
        array $criteria,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null
    ) {
        return $this->getManager()->findBy(
            $criteria,
            $orderBy,
            $limit,
            $offset,
        );
    }

    public function findOneBy(array $criteria)
    {
        return $this->getManager()->findOneBy($criteria);
    }

    public function getClassName()
    {
        return $this->getManager()->getClassName();
    }
}
