<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\Service;

use App\Application\Port\Secondary\ExternalBankClientInterface;
use App\Domain\ExternalTransfer;

/**
 * @codeCoverageIgnore
 */
final readonly class TransferToExternalBankService implements ExternalBankClientInterface
{
    public function transfer(ExternalTransfer $transfer): void
    {
        // Some long process which allows us to transfer found to different bank and get verification that it happened
        // properly
    }
}
