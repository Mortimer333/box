<?php

declare(strict_types=1);

namespace App\Application\Port\Secondary;

use App\Domain\Transfer;

interface CorInterface
{
    public function process(Transfer $transfer, BankAccountInterface $sender): void;
}
