<?php

declare(strict_types=1);

namespace App\Application\Port\Secondary;

use App\Application\Infrastructure\Exception\TransactionNotFoundException;

interface RetrieveTransactionRepositoryInterface
{
    /**
     * @throws TransactionNotFoundException
     */
    public function get(int $id): TransactionInterface;

    public function retrieveSumBetweenDateWithoutFailures(\DateTime $from, \DateTime $to, int $bankAccountId): int;
}
