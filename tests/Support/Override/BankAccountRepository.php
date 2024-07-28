<?php

declare(strict_types=1);

namespace App\Tests\Support\Override;

use App\Application\Port\Secondary\BankAccountInterface;
use App\Application\Port\Secondary\BankAccountRepositoryInterface;

class BankAccountRepository implements BankAccountRepositoryInterface
{
    public static ?BankAccountRepositoryInterface $mock = null;

    public function __construct(
        protected BankAccountRepositoryInterface $default,
    ) {
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
}
