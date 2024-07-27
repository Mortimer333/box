<?php

declare(strict_types=1);

namespace App\Application\Port\Secondary;

use App\Application\Infrastructure\Exception\BankAccountNotFoundException;

interface BankAccountRepositoryInterface
{
    /**
     * @throws BankAccountNotFoundException
     */
    public function get(int $id): BankAccountInterface;

    public function getByIdentifier(string $accountNumber): ?BankAccountInterface;

    public function lockOptimistic(int $id, ?int $version = null): ?BankAccountInterface;
}
