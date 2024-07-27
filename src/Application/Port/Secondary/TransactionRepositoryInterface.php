<?php

declare(strict_types=1);

namespace App\Application\Port\Secondary;

use App\Application\Infrastructure\Exception\TransactionNotFoundException;
use App\Domain\Transfer;

interface TransactionRepositoryInterface
{
    /**
     * @throws TransactionNotFoundException
     */
    public function get(int $id): TransactionInterface;

    public function create(Transfer $transfer, BankAccountInterface $sender): TransactionInterface;

    /**
     * @return array<TransactionInterface>
     */
    public function retrieveBetweenDate(\DateTime $from, \DateTime $to, int $limit = 10, int $offset = 0): array;
}
