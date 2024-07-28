<?php

declare(strict_types=1);

namespace App\Application\Port\Secondary;

use App\Domain\ExternalTransfer;

interface ExternalBankClientInterface
{
    public function transfer(ExternalTransfer $transfer): void;
}
