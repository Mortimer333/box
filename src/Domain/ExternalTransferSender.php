<?php

declare(strict_types=1);

namespace App\Domain;

final readonly class ExternalTransferSender
{
    public function __construct(
        public string $bankAccountNumber,
        public float $bankAccountCredit,
    ) {
    }
}
