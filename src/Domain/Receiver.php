<?php

declare(strict_types=1);

namespace App\Domain;

final readonly class Receiver
{
    public function __construct(
        public string $bankAccountNumber,
        public string $name,
        public ?string $address = null,
    ) {
    }
}
