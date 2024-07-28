<?php

declare(strict_types=1);

namespace App\Domain;

final readonly class Sender
{
    public function __construct(
        public int $userId,
        public string $bankAccountNumber,
        public float $bankAccountCredit,
        public int $transactionsDoneToday,
    ) {
    }
}
