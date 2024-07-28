<?php

declare(strict_types=1);

namespace App\Domain;

final readonly class Sender
{
    public function __construct(
        public string $bankAccountNumber,
        public float $bankAccountCredit,
        public int $transactionsDoneToday,
    ) {
    }
}
