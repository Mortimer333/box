<?php

declare(strict_types=1);

namespace App\Domain;

/**
 * @codeCoverageIgnore
 */
final readonly class Sender
{
    public function __construct(
        public int $userId,
        public string $bankAccountNumber,
        public float $credit,
        public float $reserved,
        public int $transactionsDoneToday,
    ) {
    }
}
